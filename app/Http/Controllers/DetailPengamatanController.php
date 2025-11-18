<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetailPengamatanRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\DetailPengamatanService;
use App\Http\Resources\DetailPengamatanResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class DetailPengamatanController extends Controller
{
    use ApiResponse;

    protected $detailPengamatanService;

    public function __construct(DetailPengamatanService $detailPengamatanService)
    {
        $this->detailPengamatanService = $detailPengamatanService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->detailPengamatanService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            DetailPengamatanResource::collection($data),
            'Data detail pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(DetailPengamatanRequest $request) {
        $this->detailPengamatanService->store($request);

        return $this->successResponse(
            'Berhasil menambah data detail pengamatan',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->detailPengamatanService->show($id);

        return $this->successResponseWithData(
            DetailPengamatanResource::make($data),
            'Data detail pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(DetailPengamatanRequest $request, $id) {
        $this->detailPengamatanService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data detail pengamatan',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->detailPengamatanService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data detail pengamatan',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->detailPengamatanService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data detail pengamatan',
            Response::HTTP_OK
        );
    }
}