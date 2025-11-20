<?php

namespace App\Http\Controllers;

use App\Exports\KuesionerExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\KuesionerRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\KuesionerService;
use App\Http\Resources\KuesionerResource;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class KuesionerController extends Controller
{
    use ApiResponse;

    protected $kuesionerService;

    public function __construct(KuesionerService $kuesionerService)
    {
        $this->kuesionerService = $kuesionerService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('artikel_id', description: 'artikel_id', type: 'number', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->kuesionerService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            KuesionerResource::collection($data),
            'Data kuesioner berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('artikel_id', description: 'artikel_id', type: 'number', default: '', example: '1')]
    public function export(Request $request)
    {
        try {
            return $this->kuesionerService->export($request);
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function store(KuesionerRequest $request)
    {
        $this->kuesionerService->store($request);

        return $this->successResponse(
            'Berhasil menambah data kuesioner',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->kuesionerService->show($id);

        return $this->successResponseWithData(
            KuesionerResource::make($data),
            'Data kuesioner berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->kuesionerService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data kuesioner',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->kuesionerService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data kuesioner',
            Response::HTTP_OK
        );
    }
}
