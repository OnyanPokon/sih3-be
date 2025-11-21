<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\KoordinatTelemetriImportRequest;
use App\Http\Requests\KoordinatTelemetriRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\KoordinatTelemetriService;
use App\Http\Resources\KoordinatTelemetriResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class KoordinatTelemetriController extends Controller
{
    use ApiResponse;

    protected $koordinatTelemetriService;

    public function __construct(KoordinatTelemetriService $koordinatTelemetriService)
    {
        $this->koordinatTelemetriService = $koordinatTelemetriService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('pos_pengamatan_id', description: 'Pos pengamatan Id.', type: 'number', default: '', example: '1')]
    #[QueryParameter('tanggal_mulai', description: 'Tanggal Mulai.', type: 'date', default: '', example: '31-11-2022')]
    #[QueryParameter('tanggal_akhir', description: 'Tanggal akhir.', type: 'date', default: '', example: '31-11-2022')]
    public function index(Request $request)
    {
        $data = $this->koordinatTelemetriService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            KoordinatTelemetriResource::collection($data),
            'Data koordinat telemetri berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('pos_pengamatan_id', description: 'Pos pengamatan Id.', type: 'number', default: '', example: '1')]
    #[QueryParameter('tanggal_mulai', description: 'Tanggal Mulai.', type: 'date', default: '', example: '31-11-2022')]
    #[QueryParameter('tanggal_akhir', description: 'Tanggal akhir.', type: 'date', default: '', example: '31-11-2022')]
    public function landing(Request $request)
    {
        $data = $this->koordinatTelemetriService->landing($request);

        return $this->successResponseWithData(
            KoordinatTelemetriResource::collection($data),
            'Data koordinat telemetri berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(KoordinatTelemetriRequest $request)
    {
        $this->koordinatTelemetriService->store($request);

        return $this->successResponse(
            'Berhasil menambah data koordinat telemetri',
            Response::HTTP_CREATED
        );
    }

    public function import(KoordinatTelemetriImportRequest $request)
    {
        $this->koordinatTelemetriService->import($request);

        return $this->successResponse(
            'Berhasil menambah data koordinat telemetri',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->koordinatTelemetriService->show($id);

        return $this->successResponseWithData(
            KoordinatTelemetriResource::make($data),
            'Data koordinat telemetri berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(KoordinatTelemetriRequest $request, $id)
    {
        $this->koordinatTelemetriService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data koordinat telemetri',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->koordinatTelemetriService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data koordinat telemetri',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->koordinatTelemetriService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data koordinat telemetri',
            Response::HTTP_OK
        );
    }
}
