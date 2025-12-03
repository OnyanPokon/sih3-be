<?php

namespace App\Http\Services;

use Exception;
use App\Models\Kategori;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KategoriService
{
    use FileUpload;
    protected $path = 'logo_kategori';
    protected $model;

    public function __construct(Kategori $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('nama_kategori', 'like', '%' . $search . '%');
        }

        return $data->paginate($per_page);
    }

    public function landing()
    {
        $data = $this->model->latest();

        return $data->get();
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();
            if ($request->hasFile('logo')) {
                $logo = $this->uploadPhotoAndConvertToWebp($request->file('logo'), $this->path);
                $validatedData['logo'] = $logo;
            }

            $validatedData['slug'] = str()->slug($validatedData['nama_kategori']);

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

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $data = $this->show($id);
            if ($request->hasFile('logo')) {
                $logo = $this->uploadPhotoAndConvertToWebp($request->file('logo'), $this->path);
                $validatedData['logo'] = $logo;
                if ($data->logo != 'default.png') {
                    $this->unlinkPhoto($data->logo);
                }
            }

            $validatedData['slug'] = str()->slug($validatedData['nama_kategori']);

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
