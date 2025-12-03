<?php

namespace App\Http\Services;

use Exception;
use App\Models\Kuesioner;
use App\Exceptions\ServiceException;
use App\Exports\KuesionerExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KuesionerService
{

    protected $model;

    public function __construct(Kuesioner $model)
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

        if ($artikel_id = $request->query('artikel_id')) {
            $data->where('artikel_id', $artikel_id);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();


        if ($unor_id = $request->query('unor_id')) {
            $data->whereHas('artikel', function ($query) use ($unor_id) {
                $query->where('unor_id', $unor_id);
            });
        }

        return $data->paginate($per_page);
    }

    public function export($request)
    {
        try {
            $unor = Auth::user()->personil->unor;
            $artikel_id = $request->query('artikel_id');
            $fields = ['id', 'artikel.judul', 'nama', 'deskripsi', 'instansi', 'asal', 'nilai', 'created_at'];
            $headings = ['ID', 'Artikel', 'Nama User', 'Deskripsi', 'Instansi', 'Asal', 'Nilai', 'Dibuat pada'];
            return Excel::download(new KuesionerExport($artikel_id, $fields, $headings), 'kuesioner-' . $unor->nama . '.xlsx');
        } catch (Exception $e) {
            throw $e;
        }
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

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }
        return $data;
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
