<?php

namespace App\Http\Services;

use Exception;
use App\Models\Pemetaan;
use App\Exceptions\ServiceException;
use App\Models\Kategori;
use App\Traits\UploadFileMap;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PemetaanService
{

    use UploadFileMap;

    protected $model;

    public function __construct(Pemetaan $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 100;
        $data = $this->model->where('status', 'publikasi')->latest();

        if ($search = $request->query('search')) {
            $data->where('nama_pemetaan', 'like', '%' . $search . '%');
        }

        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }

        if ($category_slug = $request->query('category_slug')) {
            $data->whereHas('kategori', function ($query) use ($category_slug) {
                $query->where('slug', $category_slug);
            });
        }

        return $data->paginate($per_page);
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('nama_pemetaan', 'like', '%' . $search . '%');
        }

        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }

        if ($kategori_id = $request->query('kategori_id')) {
            $data->where('kategori_id', $kategori_id);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $data->where('unor_id', $user->personil->unor->id);
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }

        return $data->paginate($per_page);
    }

    public function byKategori($request)
    {
        $publishedPemetaan = function ($query) use ($request) {
            $query->where('status', 'publikasi');
        };

        $kategori = Kategori::whereHas('pemetaan', $publishedPemetaan)
            ->with(['pemetaan' => $publishedPemetaan])
            ->get();

        return $kategori;
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();

            $validatedData['unor_id'] = Auth::user()->personil->unor->id;
            $validatedData['user_id'] = Auth::user()->id;

            if ($validatedData['tipe'] === 'upload') {
                if (!$request->hasFile('file')) {
                    throw new ConflictHttpException('File pemetaan harus diisi.');
                }
                $filePath = $this->handleMapUpload(
                    $request->file('file'),
                    'pemetaan'
                );

                $validatedData['file'] = $filePath;
                $validatedData['koordinat'] = null;
            } else {
                $validatedData['file'] = null;
            }

            $this->model->create($validatedData);

            return $validatedData;
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }
        return $data;
    }
    public function showGeoJson($id)
    {
        $pemetaan = $this->show($id);
        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Post-Check'    => '0',
            'Pre-Check'     => '0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT', // Tanggal di masa lalu
            'Access-Control-Allow-Origin' => '*',
        ];
        if ($pemetaan->tipe === 'manual' && !empty($pemetaan->koordinat)) {
            return response()->json($pemetaan->koordinat, 200, $headers);
        }

        if ($pemetaan->tipe === 'upload' && !empty($pemetaan->file)) {

            $filename = $pemetaan->file; // misal: "pemetaan/map-123.geojson"

            if (!Storage::disk('public')->exists($filename)) {
                return response()->json(['error' => 'File not found on disk'], 404);
            }
            $path = Storage::disk('public')->path($filename);

            $headers['Content-Type'] = 'application/geo+json';
            return response()->file($path, $headers);
        }

        return response()->json(['error' => 'No map data found for this entry'], 404);
    }

    public function customizeGeoJson($request, $id)
    {
        try {
            $pemetaan = $this->show($id);
            $geojson = $request->input('geojson');

            if (empty($geojson)) {
                throw new ConflictHttpException('Data GeoJSON tidak boleh kosong.');
            }

            $geojsonString = is_string($geojson) ? $geojson : json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // Validasi JSON
            json_decode($geojsonString);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ConflictHttpException('Format GeoJSON tidak valid.');
            }

            if ($pemetaan->tipe === 'upload' && !empty($pemetaan->file)) {
                // Backup file original jika belum ada
                if (empty($pemetaan->file_original)) {
                    if (Storage::disk('public')->exists($pemetaan->file)) {
                        $originalFileName = 'pemetaan/original_' . basename($pemetaan->file);
                        Storage::disk('public')->copy($pemetaan->file, $originalFileName);
                        $pemetaan->file_original = $originalFileName;
                    }
                }

                // Overwrite file utama dengan data GeoJSON yang dikustomisasi
                Storage::disk('public')->put($pemetaan->file, $geojsonString);
                $pemetaan->save();
            } else if ($pemetaan->tipe === 'manual') {
                $pemetaan->koordinat = is_array($geojson) ? $geojson : json_decode($geojsonString, true);
                $pemetaan->save();
            }

            return $pemetaan;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function resetGeoJson($id)
    {
        try {
            $pemetaan = $this->show($id);

            if ($pemetaan->tipe === 'upload' && !empty($pemetaan->file_original)) {
                if (Storage::disk('public')->exists($pemetaan->file_original)) {
                    Storage::disk('public')->copy($pemetaan->file_original, $pemetaan->file);
                    Storage::disk('public')->delete($pemetaan->file_original);
                }
                $pemetaan->file_original = null;
                $pemetaan->save();
            }

            return $pemetaan;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $pemetaan = $this->show($id);

            $dataToUpdate = $validatedData;

            if ($validatedData['tipe'] === 'upload') {
                if ($request->hasFile('file')) {
                    $newFilePath = $this->handleMapUpload(
                        $request->file('file'),
                        'pemetaan'
                    );
                    $this->deleteMapFile($pemetaan->file);
                    $this->deleteMapFile($pemetaan->file_original);
                    $dataToUpdate['file'] = $newFilePath;
                    $dataToUpdate['file_original'] = null;
                    $dataToUpdate['koordinat'] = null;
                } else {
                    unset($dataToUpdate['file']);
                }
            } else {
                $this->deleteMapFile($pemetaan->file);
                $this->deleteMapFile($pemetaan->file_original);
                $dataToUpdate['koordinat'] = $validatedData['koordinat'];
                $dataToUpdate['file'] = null;
                $dataToUpdate['file_original'] = null;
            }

            $pemetaan->update($dataToUpdate);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->show($id);

            $data->tipe === 'upload' ? $this->deleteMapFile($data->file) : null;
            $data->file_original ? $this->deleteMapFile($data->file_original) : null;
            $data->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function multiDestroy($ids)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->whereIn('id', explode(",", $ids))->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }

            $this->model->whereIn('id', explode(",", $ids))->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
