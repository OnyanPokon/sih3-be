<?php

namespace App\Http\Services;

use Exception;
use App\Models\Berita;
use App\Models\WilayahSungai;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class BeritaService
{
    use FileUpload;

    protected $path = 'berita';
    protected $model;

    public function __construct(Berita $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi', 'tags.kategori'])->where('status', 'publikasi')->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }
        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }
        if ($category_slug = $request->query('category_slug')) {
            $data->whereHas('kategori', function ($query) use ($category_slug) {
                $query->where('slug', $category_slug);
            });
        }

        if ($tagId = $request->query('tag_id')) {
            $data->whereHas('tags', function ($query) use ($tagId) {
                $query->where('tags.id', $tagId);
            });
        }

        if ($wilayahSungaiId = $request->query('wilayah_sungai_id')) {
            $data->whereHas('tags', function ($query) use ($wilayahSungaiId) {
                $query->where('referensi_type', WilayahSungai::class)
                    ->where('referensi_id', $wilayahSungaiId);
            });
        }

        return $data->paginate($per_page);
    }

    public function detail($slug)
    {
        $data = $this->model->with(['tags.referensi', 'tags.kategori'])->where('slug', $slug)->where('status', 'publikasi')->first();

        if (!$data) {
            throw new ServiceException('Berita tidak ditemukan', 404);
        }

        return $data;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi', 'tags.kategori'])->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }
        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }
        if ($kategori_id = $request->query('kategori_id')) {
            $data->where('kategori_id', $kategori_id);
        }

        if ($tagId = $request->query('tag_id')) {
            $data->whereHas('tags', function ($query) use ($tagId) {
                $query->where('tags.id', $tagId);
            });
        }

        if ($wilayahSungaiId = $request->query('wilayah_sungai_id')) {
            $data->whereHas('tags', function ($query) use ($wilayahSungaiId) {
                $query->where('referensi_type', WilayahSungai::class)
                    ->where('referensi_id', $wilayahSungaiId);
            });
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

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumnbail'] = $thumbnail;
            }
            $berita = $this->model->create([
                'judul' => $validatedData['judul'],
                'slug' => $validatedData['slug'],
                'konten' => $validatedData['konten'],
                'thumnbail' => $validatedData['thumnbail'] ?? 'default.png',
                'status' => $validatedData['status'],
                'unor_id' => $validatedData['unor_id'],
                'kategori_id' => $validatedData['kategori_id'],
                'user_id' => $validatedData['user_id']
            ]);

            if (isset($validatedData['tag_ids']) && is_array($validatedData['tag_ids'])) {
                $berita->syncTags($validatedData['tag_ids']);
            }
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->with(['tags.referensi', 'tags.kategori'])->find($id);

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
            $validatedData['slug'] = str()->slug($validatedData['judul']);
            $validatedData['unor_id'] = Auth::user()->personil->unor->id;
            $validatedData['user_id'] = Auth::user()->id;
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
                if ($data->thumbnail != 'default.png') {
                    $this->unlinkPhoto($data->thumbnail);
                }
            }
            $data->update([
                'judul' => $validatedData['judul'],
                'slug' => $validatedData['slug'],
                'konten' => $validatedData['konten'],
                'thumnbail' => $validatedData['thumbnail'] ?? $data->thumnbail,
                'unor_id' => $validatedData['unor_id'],
                'kategori_id' => $validatedData['kategori_id'],
                'user_id' => $validatedData['user_id'],
                'status' => $request->status ? $validatedData['status'] : $data->status
            ]);

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
            if ($data->thumbnail != 'default.png') {
                $this->unlinkPhoto($data->thumbnail);
            }
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
