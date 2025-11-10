<?php

namespace App\Http\Services;

use Exception;
use App\Models\ProdukHukum;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProdukHukumService
{
    use FileUpload;
    protected $model;

    protected $path = 'produk_hukum';

    public function __construct(ProdukHukum $model)
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

    public function detail($slug)
    {
        $data = $this->model->where('slug', $slug)->where('status', 'publikasi')->first();

        if (!$data) {
            throw new ServiceException('Produk Hukum tidak ditemukan', 404);
        }

        return $data;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($tipe = $request->query('tipe')) {
            $data->where('tipe', $tipe);
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

            if ($request->hasFile('file')) {
                $file = $this->uploadDocument($request->file('file'), ['pdf'], $this->path);
                $validatedData['file'] = $file;
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
            $validatedData['slug'] = str()->slug($validatedData['judul']);
            $validatedData['unor_id'] = Auth::user()->personil->unor->id;
            $validatedData['user_id'] = Auth::user()->id;

            if ($request->hasFile('file')) {
                $file = $this->uploadDocument($request->file('file'), ['pdf'], $this->path);
                $validatedData['file'] = $file;
                $this->unlinkPhoto($data->file);
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
            $this->unlinkPhoto($data->file);
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
