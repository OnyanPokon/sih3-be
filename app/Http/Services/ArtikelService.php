<?php

namespace App\Http\Services;

use Exception;
use App\Models\Artikel;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArtikelService
{

    use FileUpload;
    protected $path = 'thumbnail_artikel';
    protected $model;

    public function __construct(Artikel $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 100;
        $data = $this->model->where('status', 'publikasi')->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }

        if ($kategori = $request->query('kategori')) {
            $data->where('kategori', $kategori);
        }

        return $data->paginate($per_page);
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }

        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }

        if ($kategori = $request->query('kategori')) {
            $data->where('kategori', $kategori);
        }

        return $data->paginate($per_page);
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['slug'] = str()->slug($validatedData['judul']);
            $validatedData['unor_id'] = Auth::user()->personil->unor->id;
            $validatedData['user_id'] = Auth::user()->id;
            $validatedData['status'] = 'publikasi';
            $validatedData['tanggal_publikasi'] = now();

            $artikel = $this->model->create([
                'judul' => $validatedData['judul'],
                'slug' => $validatedData['slug'],
                'konten' => $validatedData['konten'],
                'status' => $validatedData['status'],
                'tanggal_publikasi' => $validatedData['tanggal_publikasi'],
                'kategori_id' => $validatedData['kategori_id'],
                'unor_id' => $validatedData['unor_id'],
                'user_id' => $validatedData['user_id'],
            ]);

            if ($request->hasFile('thumbnails')) {
                foreach ($request->file('thumbnails') as $file) {

                    $path = $this->uploadPhotoAndConvertToWebp($file, $this->path);

                    $artikel->thumbnails()->create([
                        'thumbnail' => $path
                    ]);
                }
            }

            return $validatedData;
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            throw new ServiceException('Data tidak ditemukan', 404);
        }
        return $data;
    }

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $data = $this->show($id);
            $data->update($validatedData);

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->show($id);
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
