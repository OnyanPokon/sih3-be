<?php

namespace App\Http\Services;

use Exception;
use App\Models\ThumbnailArtikel;
use App\Exceptions\ServiceException;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ThumbnailArtikelService
{
    use FileUpload;

    protected $path = 'thumbnail_artikel';

    protected $model;

    public function __construct(ThumbnailArtikel $model)
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

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
            }
            $this->model->create($validatedData);
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

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
                if ($data->thumbnail != 'default.png') {
                    $this->unlinkPhoto($data->foto);
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
}
