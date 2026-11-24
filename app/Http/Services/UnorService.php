<?php

namespace App\Http\Services;

use Exception;
use App\Models\Unor;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnorService
{
    use FileUpload;
    protected $model;
    protected $path = 'logo_unor';

    public function __construct(Unor $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        return $data->paginate($per_page);
    }

    public function landing($request)
    {
        $data = $this->model->where('status', 'aktif')->latest();

        if ($categorySlug = $request->query('category_slug')) {
        $data->whereHas('kategoris', function ($query) use ($categorySlug) {
            $query->where('slug', $categorySlug);
        });
    }

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
