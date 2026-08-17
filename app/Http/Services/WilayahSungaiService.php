<?php

namespace App\Http\Services;

use Exception;
use App\Models\WilayahSungai;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use App\Traits\FileUpload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WilayahSungaiService
{
    use FileUpload;

    protected $model;
    protected $path = 'thumbnail_wilayah_sungai';

    public function __construct(WilayahSungai $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $user = auth()->user();

        $data = $this->model->with(['kategori', 'unor']);

        // Hanya filter berdasarkan Unor & Kategori jika user memiliki role admin (bukan superadmin)
        if ($user && $user->hasRole('admin')) {
            $unorId = $user->personil?->unor_id;

            if (!$unorId) {
                return $request->query('per_page') === 'all'
                    ? $this->model->whereRaw('1 = 0')->get()
                    : $this->model->whereRaw('1 = 0')->paginate(10);
            }

            // Ambil kategori yang terasosiasi dengan unor user
            $categoryIds = DB::table('kategori_unor')
                ->where('unor_id', $unorId)
                ->pluck('kategori_id');

            if ($categoryIds->isEmpty()) {
                return $request->query('per_page') === 'all'
                    ? $this->model->whereRaw('1 = 0')->get()
                    : $this->model->whereRaw('1 = 0')->paginate(10);
            }

            $data->where('unor_id', $unorId)
                ->whereIn('kategori_id', $categoryIds);
        }

        $data->latest();

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($request->query('per_page') === 'all') {
            return $data->get();
        }

        return $data->paginate((int)$per_page);
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();
            $user = auth()->user();
            $unorId = $user->personil?->unor_id;

            if (!$unorId) {
                throw new Exception('User tidak memiliki Unit Organisasi (Unor) yang valid.');
            }

            $validatedData['unor_id'] = $unorId;

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
            }

            return $this->model->create($validatedData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = $this->model->with(['kategori', 'unor']);

        if ($user->hasRole('admin')) {
            $unorId = $user->personil?->unor_id;
            $query->where('unor_id', $unorId);
        }

        $data = $query->find($id);

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

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
                if ($data->thumbnail != 'default.png') {
                    $this->unlinkPhoto($data->thumbnail);
                }
            }

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
            $user = auth()->user();
            $unorId = $user->personil?->unor_id;

            $data = $this->model->where('unor_id', $unorId)
                ->whereIn('id', explode(",", $ids))
                ->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }

            foreach ($data as $item) {
                if ($item->thumbnail != 'default.png') {
                    $this->unlinkPhoto($item->thumbnail);
                }
                $item->delete();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function landing($request)
    {
        $query = $this->model->with(['kategori', 'unor']);

        if ($kategoriSlug = $request->query('kategori')) {
            $query->whereHas('kategori', function ($q) use ($kategoriSlug) {
                $q->where('slug', $kategoriSlug);
            });
        }

        if ($kategoriId = $request->query('kategori_id')) {
            $query->where('kategori_id', $kategoriId);
        }

        return $query->latest()->get();
    }
}
