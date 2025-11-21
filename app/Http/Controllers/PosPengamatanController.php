<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosPengamatanImportRequest;
use App\Http\Requests\PosPengamatanRequest;
use App\Http\Resources\PosPengamatanResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\PosPengamatanService;
use Dedoc\Scramble\Attributes\QueryParameter;


class PosPengamatanController extends Controller
{
    use ApiResponse;

    protected $posPengamatanService;

    public function __construct(PosPengamatanService $posPengamatanService)
    {
        $this->posPengamatanService = $posPengamatanService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('jenis_pos_id', description: 'jenis pos id.', type: 'number', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->posPengamatanService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            PosPengamatanResource::collection($data),
            'Data pos pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('jenis_pos_id', description: 'jenis pos id.', type: 'number', default: '', example: '1')]
    public function landing(Request $request)
    {
        $data = $this->posPengamatanService->landing($request);

        return $this->successResponseWithDataIndex(
            $data,
            PosPengamatanResource::collection($data),
            'Data pos pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(PosPengamatanRequest $request)
    {
        $this->posPengamatanService->store($request);

        return $this->successResponse(
            'Berhasil menambah data pos pengamatan',
            Response::HTTP_CREATED
        );
    }

    public function import(PosPengamatanImportRequest $request)
    {
        $this->posPengamatanService->import($request);

        return $this->successResponse(
            'Berhasil menambah data pos pengamatan',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->posPengamatanService->show($id);

        return $this->successResponseWithData(
            PosPengamatanResource::make($data),
            'Data pos pengamatan berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(PosPengamatanRequest $request, $id)
    {
        $this->posPengamatanService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data pos pengamatan',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->posPengamatanService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data pos pengamatan',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->posPengamatanService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data pos pengamatan',
            Response::HTTP_OK
        );
    }
}
