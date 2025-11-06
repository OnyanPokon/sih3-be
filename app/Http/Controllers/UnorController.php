<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unor\UnorRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\UnorService;
use App\Http\Resources\UnorResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class UnorController extends Controller
{
    use ApiResponse;

    protected $unorService;

    public function __construct(UnorService $unorService)
    {
        $this->unorService = $unorService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request)
    {
        $data = $this->unorService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            UnorResource::collection($data),
            'Data unor berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(UnorRequest $request)
    {
        $this->unorService->store($request);

        return $this->successResponse(
            'Berhasil menambah data unor',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->unorService->show($id);

        return $this->successResponseWithData(
            UnorResource::make($data),
            'Data unor berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(UnorRequest $request, $id)
    {
        $this->unorService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data unor',
            Response::HTTP_OK
        );
    }

    public function updateProfil(UnorRequest $request, $id)
    {
        $this->unorService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data unor',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->unorService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data unor',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->unorService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data unor',
            Response::HTTP_OK
        );
    }
}
