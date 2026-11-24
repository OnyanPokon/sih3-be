<?php

namespace App\Http\Services;

use App\Imports\PosPengamatanImport;
use App\Models\Kategori;
use Exception;
use App\Models\PosPengamatan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PosPengamatanService
{

    protected $model;

    public function __construct(PosPengamatan $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi', 'jenis_pos'])->latest();

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($jenis_pos_id = $request->query('jenis_pos_id')) {
            $data->where('jenis_pos_id', $jenis_pos_id);
        }

        return $data->paginate($per_page);
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['tags.referensi', 'jenis_pos'])->latest();

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($jenis_pos_id = $request->query('jenis_pos_id')) {
            $data->where('jenis_pos_id', $jenis_pos_id);
        }

        return $data->paginate($per_page);
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();
            $pos = $this->model->create($validatedData);

            if (isset($validatedData['tag_ids']) && is_array($validatedData['tag_ids'])) {
                $pos->syncTags($validatedData['tag_ids']);
            }

            return $validatedData;
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function import($request)
    {
        try {
            $validatedData = $request->validated();
            $file = $validatedData['file'];

            $collection = Excel::toCollection(null, $file)->first();

            if ($collection->isEmpty()) {
                throw new Exception('File Excel kosong atau tidak memiliki data.');
            }
            $nama_kategori_mentah = $collection->get(0)->get(0) ?? null;

            if (empty($nama_kategori_mentah)) {
                throw new Exception('Nama Kategori pada Baris 1 Excel tidak boleh kosong.');
            }

            $nama_kategori_normalisasi = strtolower(trim($nama_kategori_mentah));

            $kategori = Kategori::where(DB::raw('LOWER(nama_kategori)'), $nama_kategori_normalisasi)->first();

            if (!$kategori) {
                throw new Exception("Kategori '{$nama_kategori_mentah}' tidak ditemukan dalam database.");
            }
            $kategori_id = $kategori->id;

            Excel::import(new PosPengamatanImport($kategori_id), $file);

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->with(['tags.referensi', 'jenis_pos'])->find($id);

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
