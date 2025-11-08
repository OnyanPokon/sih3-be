<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Artikel\StoreArtikelRequest;
use App\Http\Requests\Artikel\UpdateArtikelRequest;
use App\Http\Requests\ArtikelRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\ArtikelService;
use App\Http\Resources\ArtikelResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class ArtikelController extends Controller
{
    use ApiResponse;

    protected $artikelService;

    public function __construct(ArtikelService $artikelService)
    {
        $this->artikelService = $artikelService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('status', description: 'The status query.', type: 'string', default: '', example: 'publikasi, draft')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->artikelService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            ArtikelResource::collection($data),
            'Data artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('status', description: 'The status query.', type: 'string', default: '', example: 'publikasi, draft')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]
    public function landing(Request $request)
    {
        $data = $this->artikelService->landing($request);

        return $this->successResponseWithDataIndex(
            $data,
            ArtikelResource::collection($data),
            'Data artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(StoreArtikelRequest $request)
    {
        $this->artikelService->store($request);

        return $this->successResponse(
            'Berhasil menambah data artikel',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->artikelService->show($id);

        return $this->successResponseWithData(
            ArtikelResource::make($data),
            'Data artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(UpdateArtikelRequest $request, $id)
    {
        $this->artikelService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data artikel',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->artikelService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data artikel',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->artikelService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data artikel',
            Response::HTTP_OK
        );
    }
}
