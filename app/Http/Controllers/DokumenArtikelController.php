<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DokumenArtikelRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\DokumenArtikelService;
use App\Http\Resources\DokumenArtikelResource;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DokumenArtikelController extends Controller
{
    use ApiResponse;

    protected $dokumenArtikelService;

    public function __construct(DokumenArtikelService $dokumenArtikelService)
    {
        $this->dokumenArtikelService = $dokumenArtikelService;
    }

    #[QueryParameter('artikel_id', description: 'Artikel Id', type: 'integer', default: '', example: '1')]
    public function index(Request $request)
    {
        $data = $this->dokumenArtikelService->getAll($request);

        return $this->successResponseWithData(
            DokumenArtikelResource::collection($data),
            'Data dokumen artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(DokumenArtikelRequest $request)
    {
        $this->dokumenArtikelService->store($request);

        return $this->successResponse(
            'Berhasil menambah data dokumen artikel',
            Response::HTTP_CREATED
        );
    }

    public function download($id)
    {
        try {
            $data = $this->dokumenArtikelService->download($id);
            return Storage::disk('public')->download(
                $data['storagePath'],
                $data['publicName']
            );
        } catch (ConflictHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            abort(404, 'File tidak ditemukan.');
        }
    }

    public function show($id)
    {
        $data = $this->dokumenArtikelService->show($id);

        return $this->successResponseWithData(
            DokumenArtikelResource::make($data),
            'Data dokumen artikel berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function update(DokumenArtikelRequest $request, $id)
    {
        $this->dokumenArtikelService->update($request, $id);

        return $this->successResponse(
            'Berhasil mengubah data dokumen artikel',
            Response::HTTP_OK
        );
    }

    public function destroy($id)
    {
        $this->dokumenArtikelService->destroy($id);

        return $this->successResponse(
            'Berhasil menghapus data dokumen artikel',
            Response::HTTP_OK
        );
    }

    public function multiDestroy(Request $request)
    {
        $this->dokumenArtikelService->multiDestroy($request->ids);

        return $this->successResponse(
            'Berhasil menghapus data dokumen artikel',
            Response::HTTP_OK
        );
    }
}
