<?php

namespace App\Http\Services;

use Exception;
use App\Models\DetailPengamatan;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DetailPengamatanService
{

    protected $model;

    public function __construct(DetailPengamatan $model)
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

        if ($pos_pengamatan_id = $request->query('pos_pengamatan_id')) {
            $data->where('pos_pengamatan_id', $pos_pengamatan_id);
        }

        return $data->paginate($per_page);
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();
            $this->model->create($validatedData);
            return $validatedData;
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function import($request)
    {
        try {
            $validatedData = $request->validated();
           
            
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
