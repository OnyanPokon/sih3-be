<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriUnorRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\KategoriUnorService;
use App\Http\Resources\KategoriUnorResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class KategoriUnorController extends Controller
{
    use ApiResponse;

    protected $kategoriUnorService;

    public function __construct(KategoriUnorService $kategoriUnorService)
    {
        $this->kategoriUnorService = $kategoriUnorService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->kategoriUnorService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            KategoriUnorResource::collection($data),
            'Data kategori unor berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(KategoriUnorRequest $request) {
        $this->kategoriUnorService->store($request);

        return $this->successResponse(
            'Berhasil menambah data kategori unor',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->kategoriUnorService->show($id);

        return $this->successResponseWithData(
            KategoriUnorResource::make($data),
            'Data kategori unor berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(KategoriUnorRequest $request, $id) {
        $this->kategoriUnorService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data kategori unor',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->kategoriUnorService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data kategori unor',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->kategoriUnorService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data kategori unor',
            Response::HTTP_OK
        );
    }
}