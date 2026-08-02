<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\TagService;
use App\Http\Resources\TagResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class TagController extends Controller
{
    use ApiResponse;

    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->tagService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            TagResource::collection($data),
            'Data tag berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(TagRequest $request) {
        $this->tagService->store($request);

        return $this->successResponse(
            'Berhasil menambah data tag',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->tagService->show($id);

        return $this->successResponseWithData(
            TagResource::make($data),
            'Data tag berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(TagRequest $request, $id) {
        $this->tagService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data tag',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->tagService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data tag',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->tagService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data tag',
            Response::HTTP_OK
        );
    }
}