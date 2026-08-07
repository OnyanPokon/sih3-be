<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeritaRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\BeritaService;
use App\Http\Resources\BeritaResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class BeritaController extends Controller
{
    use ApiResponse;

    protected $beritaService;

    public function __construct(BeritaService $beritaService)
    {
        $this->beritaService = $beritaService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('status', description: 'The search query.', type: 'string', default: '', example: 'publikasi,draft')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('tag_id', description: 'Filter berita berdasarkan relasi tag id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('wilayah_sungai_id', description: 'Filter berita berdasarkan tag dengan referensi wilayah sungai.', type: 'integer', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->beritaService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            BeritaResource::collection($data),
            'Data berita berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('unor_id', description: 'The unor id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('kategori_id', description: 'The kategori id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('tag_id', description: 'Filter berita berdasarkan relasi tag id.', type: 'integer', default: '', example: '1')]
    #[QueryParameter('wilayah_sungai_id', description: 'Filter berita berdasarkan tag dengan referensi wilayah sungai.', type: 'integer', default: '', example: '1')]
    public function landing(Request $request)
    {
        $data = $this->beritaService->landing($request);

        return $this->successResponseWithDataIndex(
            $data,
            BeritaResource::collection($data),
            'Data berita berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function detail($slug)
    {
        $data = $this->beritaService->detail($slug);

        return $this->successResponseWithData(
            BeritaResource::make($data),
            'Data berita berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(BeritaRequest $request)
    {
        $this->beritaService->store($request);

        return $this->successResponse(
            'Berhasil menambah data berita',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->beritaService->show($id);

        return $this->successResponseWithData(
            BeritaResource::make($data),
            'Data berita berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(BeritaRequest $request, $id)
    {
        $this->beritaService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data berita',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->beritaService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data berita',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->beritaService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data berita',
            Response::HTTP_OK
        );
    }
}
