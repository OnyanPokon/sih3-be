<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\RoleService;
use App\Http\Resources\RoleResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class RoleController extends Controller
{
    use ApiResponse;

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->roleService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            RoleResource::collection($data),
            'Data role berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(RoleRequest $request) {
        $this->roleService->store($request);

        return $this->successResponse(
            'Berhasil menambah data role',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->roleService->show($id);

        return $this->successResponseWithData(
            RoleResource::make($data),
            'Data role berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(RoleRequest $request, $id) {
        $this->roleService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data role',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->roleService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data role',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->roleService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data role',
            Response::HTTP_OK
        );
    }
}