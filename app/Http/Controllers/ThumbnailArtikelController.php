<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThumbnailArtikelRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\ThumbnailArtikelService;
use App\Http\Resources\ThumbnailArtikelResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class ThumbnailArtikelController extends Controller
{
    use ApiResponse;

    protected $thumbnailArtikelService;

    public function __construct(ThumbnailArtikelService $thumbnailArtikelService)
    {
        $this->thumbnailArtikelService = $thumbnailArtikelService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('artikel_id', description: 'The artikel id.', type: 'integer', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->thumbnailArtikelService->getAll($request);

        return $this->successResponseWithData(
            ThumbnailArtikelResource::collection($data),
            'Data thumbnail artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(ThumbnailArtikelRequest $request)
    {
        $this->thumbnailArtikelService->store($request);

        return $this->successResponse(
            'Berhasil menambah data thumbnail artikel',
            Response::HTTP_OK
        );
    }

    public function show($id)
    {
        $data = $this->thumbnailArtikelService->show($id);

        return $this->successResponseWithData(
            ThumbnailArtikelResource::make($data),
            'Data thumbnail artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(ThumbnailArtikelRequest $request, $id)
    {
        $this->thumbnailArtikelService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data thumbnail artikel',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->thumbnailArtikelService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data thumbnail artikel',
            Response::HTTP_OK
        );
    }
}
