<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\JenisPosRequest;
use App\Http\Requests\PosPengamatanRequest;
use App\Http\Resources\JenisPosResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\JenisPosService;
use Dedoc\Scramble\Attributes\QueryParameter;


class JenisPosController extends Controller
{
    use ApiResponse;

    protected $jenisPosService;

    public function __construct(JenisPosService $jenisPosService)
    {
        $this->jenisPosService = $jenisPosService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->jenisPosService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            JenisPosResource::collection($data),
            'Data pos pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(JenisPosRequest $request)
    {
        $this->jenisPosService->store($request);

        return $this->successResponse(
            'Berhasil menambah data pos pengamatan',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->jenisPosService->show($id);

        return $this->successResponseWithData(
            JenisPosResource::make($data),
            'Data pos pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(JenisPosRequest $request, $id)
    {
        $this->jenisPosService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data pos pengamatan',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->jenisPosService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data pos pengamatan',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->jenisPosService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data pos pengamatan',
            Response::HTTP_OK
        );
    }
}
