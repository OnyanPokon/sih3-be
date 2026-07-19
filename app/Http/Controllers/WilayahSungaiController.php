<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\WilayahSungaiRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\WilayahSungaiService;
use App\Http\Resources\WilayahSungaiResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class WilayahSungaiController extends Controller
{
    use ApiResponse;

    protected $wilayahSungaiService;

    public function __construct(WilayahSungaiService $wilayahSungaiService)
    {
        $this->wilayahSungaiService = $wilayahSungaiService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->wilayahSungaiService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            WilayahSungaiResource::collection($data),
            'Data wilayah sungai berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function landing(Request $request) {
        $data = $this->wilayahSungaiService->landing($request);

        return $this->successResponseWithData(
            WilayahSungaiResource::collection($data),
            'Data wilayah sungai berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(WilayahSungaiRequest $request) {
        $this->wilayahSungaiService->store($request);

        return $this->successResponse(
            'Berhasil menambah data wilayah sungai',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->wilayahSungaiService->show($id);

        return $this->successResponseWithData(
            WilayahSungaiResource::make($data),
            'Data wilayah sungai berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(WilayahSungaiRequest $request, $id) {
        $this->wilayahSungaiService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data wilayah sungai',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->wilayahSungaiService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data wilayah sungai',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->wilayahSungaiService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data wilayah sungai',
            Response::HTTP_OK
        );
    }
}