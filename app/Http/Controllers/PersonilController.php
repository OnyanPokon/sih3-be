<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personil\StorePersonilRequest;
use App\Http\Requests\Personil\UpdatePersonilRequest;
use App\Http\Requests\Personil\UpdateProfilPersonilRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\PersonilService;
use App\Http\Resources\PersonilResource;
use Dedoc\Scramble\Attributes\QueryParameter;


class PersonilController extends Controller
{
    use ApiResponse;

    protected $personilService;

    public function __construct(PersonilService $personilService)
    {
        $this->personilService = $personilService;
    }

    #[QueryParameter('per_page', description: 'Number of items per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('page', description: 'The page number.', type: 'int', default: 1, example: 1)]
    #[QueryParameter('search', description: 'The search query.', type: 'string', default: '', example: 'title')]
    #[QueryParameter('unor_id', description: 'filter by unor.', type: 'integer', default: '', example: 1)]
    #[QueryParameter('status', description: 'filter by status.', type: 'string', default: '', example: ['aktif', 'nonaktif'])]
    public function index(Request $request)
    {
        $data = $this->personilService->getAll($request);

        return $this->successResponseWithDataIndex(
            $data,
            PersonilResource::collection($data),
            'Data personil berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(StorePersonilRequest $request)
    {
        $this->personilService->store($request);

        return $this->successResponse(
            'Berhasil menambah data personil',
            Response::HTTP_CREATED
        );
    }

    public function show($id)
    {
        $data = $this->personilService->show($id);

        return $this->successResponseWithData(
            PersonilResource::make($data),
            'Data personil berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(UpdatePersonilRequest $request, $id)
    {
        $this->personilService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data personil',
            Response::HTTP_OK
        );
    }

    public function resetPassword(Request $request, $id)
    {
        $this->personilService->resetPassword($request, $id);

        return $this->successResponse(
            'Berhasil mereset password personil',
            Response::HTTP_OK
        );
    }

    public function updateProfil(UpdateProfilPersonilRequest $request, $id)
    {
        $this->personilService->updateProfil($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data personil',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->personilService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data personil',
            Response::HTTP_OK
        );
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:aktif,nonaktif',
        ]);

        $this->personilService->updateStatus($id, $request->status);

        return $this->successResponse(
            'Berhasil mengubah status personil',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->personilService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data personil',
            Response::HTTP_OK
        );
    }
}
