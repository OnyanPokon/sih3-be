<?php

namespace App\Http\Services;

use Exception;
use App\Models\JenisPos;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JenisPosService
{
    protected $model;

    public function __construct(JenisPos $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi', 'kategori', 'unor', 'user'])->latest();

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%')->orWhere('kewenangan', 'like', '%' . $search . '%');
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

        if ($request->query('per_page') === 'all') {
            return $data->get();
        }

        return $data->paginate((int)$per_page);
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi', 'kategori', 'unor', 'user'])->latest();

        if ($search = $request->query('search')) {
            $data->where('nama_pos', 'like', '%' . $search . '%')->orWhere('kewenangan', 'like', '%' . $search . '%');
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

        if ($request->query('per_page') === 'all') {
            return $data->get();
        }

        return $data->paginate((int)$per_page);
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();

            $validatedData['unor_id'] = Auth::user()->personil->unor->id;
            $validatedData['user_id'] = Auth::user()->id;
            $validatedData['slug'] = str()->slug($validatedData['nama']);

            $tagIds = $validatedData['tag_ids'] ?? null;
            unset($validatedData['tag_ids']);

            $jenisPos = $this->model->create($validatedData);

            if (is_array($tagIds)) {
                $jenisPos->syncTags($tagIds);
            }

            return $jenisPos;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->with(['tags.referensi', 'kategori', 'unor', 'user'])->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }
        return $data;
    }

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['unor_id'] = Auth::user()->personil->unor->id;
            $validatedData['user_id'] = Auth::user()->id;
            $validatedData['slug'] = str()->slug($validatedData['nama']);

            $tagIds = $validatedData['tag_ids'] ?? null;
            unset($validatedData['tag_ids']);

            $data = $this->show($id);

            $data->update($validatedData);

            if (is_array($tagIds)) {
                $data->syncTags($tagIds);
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
