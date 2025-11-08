<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdukHukumRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\ProdukHukumService;
use App\Http\Resources\ProdukHukumResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class ProdukHukumController extends Controller
{
    use ApiResponse;

    protected $produkHukumService;

    public function __construct(ProdukHukumService $produkHukumService)
    {
        $this->produkHukumService = $produkHukumService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->produkHukumService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            ProdukHukumResource::collection($data),
            'Data produk hukum berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(ProdukHukumRequest $request) {
        $this->produkHukumService->store($request);

        return $this->successResponse(
            'Berhasil menambah data produk hukum',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->produkHukumService->show($id);

        return $this->successResponseWithData(
            ProdukHukumResource::make($data),
            'Data produk hukum berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(ProdukHukumRequest $request, $id) {
        $this->produkHukumService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data produk hukum',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->produkHukumService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data produk hukum',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->produkHukumService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data produk hukum',
            Response::HTTP_OK
        );
    }
}