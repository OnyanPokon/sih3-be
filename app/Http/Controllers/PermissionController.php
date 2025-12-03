<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\PermissionService;
use App\Http\Resources\PermissionResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class PermissionController extends Controller
{
    use ApiResponse;

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->permissionService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            PermissionResource::collection($data),
            'Data permission berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(PermissionRequest $request) {
        $this->permissionService->store($request);

        return $this->successResponse(
            'Berhasil menambah data permission',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->permissionService->show($id);

        return $this->successResponseWithData(
            PermissionResource::make($data),
            'Data permission berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(PermissionRequest $request, $id) {
        $this->permissionService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data permission',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->permissionService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data permission',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->permissionService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data permission',
            Response::HTTP_OK
        );
    }
}