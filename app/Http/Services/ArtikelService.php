<?php

namespace App\Http\Services;

use Exception;
use App\Models\Artikel;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArtikelService
{

    use FileUpload;
    protected $path_thumbnail = 'thumbnail_artikel';
    protected $path_dokumen = 'dokumen_artikel';
    protected $model;

    public function __construct(Artikel $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 100;
        $data = $this->model->with(['tags.referensi'])->where('status', 'publikasi')->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
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

    public function detail($slug)
    {
        $data = $this->model->with(['tags.referensi'])
            ->where('slug', $slug)
            ->where('status', 'publikasi')
            ->first();

        if (!$data) {
            throw new NotFoundHttpException('Artikel tidak ditemukan');
        }

        $data->increment('total_kunjungan');

        return $data;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi'])->latest();

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

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $data->where('unor_id', $user->personil->unor->id);
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
                'kuesioner_tambahan' => $validatedData['kuesioner_tambahan'] ?? true,
                'tanggal_publikasi' => $validatedData['tanggal_publikasi'],
                'kategori_id' => $validatedData['kategori_id'],
                'unor_id' => $validatedData['unor_id'],
                'user_id' => $validatedData['user_id'],
            ]);

            if (isset($validatedData['tag_ids']) && is_array($validatedData['tag_ids'])) {
                $artikel->syncTags($validatedData['tag_ids']);
            }

            if ($request->hasFile('thumbnails')) {
                foreach ($request->file('thumbnails') as $file) {
                    $thumbnail_artikel = $this->uploadPhotoAndConvertToWebp($file, $this->path_thumbnail);

                    $artikel->thumbnails()->create([
                        'thumbnail' => $thumbnail_artikel
                    ]);
                }
            }

            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $file) {
                    $dokumen_artikel = $this->uploadDocument($file, ['pdf'], $this->path_dokumen);
                    $artikel->dokumen()->create([
                        'nama_dokumen' => $file->getClientOriginalName(),
                        'dokumen' => $dokumen_artikel
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
        $data = $this->model->with(['tags.referensi'])->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }
        return $data;
    }

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $data = $this->show($id);
            $data->update($validatedData);

            if (isset($validatedData['tag_ids']) && is_array($validatedData['tag_ids'])) {
                $data->syncTags($validatedData['tag_ids']);
            }

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
