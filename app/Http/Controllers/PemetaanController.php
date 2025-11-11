<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pemetaan\StorePemetaanRequest;
use App\Http\Requests\Pemetaan\UpdatePemetaanRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\PemetaanService;
use App\Http\Resources\PemetaanResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class PemetaanController extends Controller
{
    use ApiResponse;

    protected $pemetaanService;

    public function __construct(PemetaanService $pemetaanService)
    {
        $this->pemetaanService = $pemetaanService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('status', description: 'The status query.', type: 'string', default: '', example: 'publikasi, privasi')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]

    public function index(Request $request)
    {
        $data = $this->pemetaanService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            PemetaanResource::collection($data),
            'Data pemetaan berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('status', description: 'The status query.', type: 'string', default: '', example: 'publikasi, privasi')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]

    public function landing(Request $request)
    {
        $data = $this->pemetaanService->landing($request);

        return $this->successResponseWithDataIndex(
            $data,
            PemetaanResource::collection($data),
            'Data pemetaan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function byKategori(Request $request)
    {
        $data = $this->pemetaanService->byKategori($request);

        return $this->successResponseWithData(
            $data,
            'Data pemetaan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(StorePemetaanRequest $request)
    {
        $this->pemetaanService->store($request);

        return $this->successResponse(
            'Berhasil menambah data pemetaan',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->pemetaanService->show($id);

        return $this->successResponseWithData(
            PemetaanResource::make($data),
            'Data pemetaan berhasil diambil',
            Response::HTTP_OK
        );
    }
    public function showGeoJson($id)
    {
        $data = $this->pemetaanService->showGeoJson($id);

        return $data;
    }

    public function update(UpdatePemetaanRequest $request, $id)
    {
        $this->pemetaanService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data pemetaan',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->pemetaanService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data pemetaan',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->pemetaanService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data pemetaan',
            Response::HTTP_OK
        );
    }
}
