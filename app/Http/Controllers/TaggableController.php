<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaggableRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\TaggableService;
use App\Http\Resources\TaggableResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class TaggableController extends Controller
{
    use ApiResponse;

    protected $taggableService;

    public function __construct(TaggableService $taggableService)
    {
        $this->taggableService = $taggableService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    public function index(Request $request) {
        $data = $this->taggableService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            TaggableResource::collection($data),
            'Data taggable berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(TaggableRequest $request) {
        $this->taggableService->store($request);

        return $this->successResponse(
            'Berhasil menambah data taggable',
            Response::HTTP_CREATED
        );
    }

    public function show($id) {
        $data = $this->taggableService->show($id);

        return $this->successResponseWithData(
            TaggableResource::make($data),
            'Data taggable berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(TaggableRequest $request, $id) {
        $this->taggableService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data taggable',
            Response::HTTP_OK
        );
    }

    public function destroy($id) {
        $this->taggableService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data taggable',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request) {
        $this->taggableService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data taggable',
            Response::HTTP_OK
        );
    }
}