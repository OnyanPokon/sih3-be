<?php

namespace App\Http\Services;

use Exception;
use App\Models\DokumenArtikel;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DokumenArtikelService
{
    use FileUpload;
    protected $path = 'dokumen_artikel';

    protected $model;

    public function __construct(DokumenArtikel $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $data = $this->model->latest();

        if ($artikel_id = $request->query('artikel_id')) {
            $data->where('artikel_id', $artikel_id);
        }

        return $data->get();
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();

            if ($request->hasFile('dokumen')) {
                $dokumen = $this->uploadDocument($request->file('dokumen'), ['pdf'], $this->path);
                $validatedData['dokumen'] = $dokumen;
            }

            $this->model->create($validatedData);
            return $validatedData;
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function download($id)
    {
        $data = $this->show($id);
        if (!Storage::disk('public')->exists($data->dokumen)) {
            abort(404, 'File tidak ditemukan.');
        }
        if ($data->artikel->status == 'draft') {
            throw new ConflictHttpException('Artikel tidak dipublikasi');
        }
        $data->artikel->increment('total_unduh');

        return [
            'storagePath' => $data->dokumen,
            'publicName' => basename($data->dokumen)
        ];
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
            if ($request->hasFile('dokumen')) {
                $dokumen = $this->uploadDocument($request->file('dokumen'), ['pdf'], $this->path);
                $validatedData['dokumen'] = $dokumen;
                if ($data->dokumen) {
                    $this->unlinkPhoto($data->dokumen);
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
            if ($data->dokumen) {
                $this->unlinkPhoto($data->dokumen);
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
