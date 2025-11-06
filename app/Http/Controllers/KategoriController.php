<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\KategoriService;
use App\Http\Resources\KategoriResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class KategoriController extends Controller
{
    use ApiResponse;

    protected $kategoriService;

    public function __construct(KategoriService $kategoriService)
    {
        $this->kategoriService = $kategoriService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->kategoriService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            KategoriResource::collection($data),
            'Data kategori berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(KategoriRequest $request) {
        $this->kategoriService->store($request);

        return $this->successResponse(
            'Berhasil menambah data kategori',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->kategoriService->show($id);

        return $this->successResponseWithData(
            KategoriResource::make($data),
            'Data kategori berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(KategoriRequest $request, $id) {
        $this->kategoriService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data kategori',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->kategoriService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data kategori',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->kategoriService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data kategori',
            Response::HTTP_OK
        );
    }
}