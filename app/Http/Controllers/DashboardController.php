<?php

namespace App\Http\Controllers;

use App\Http\Services\DashboardService;
use App\Traits\ApiResponse;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    use ApiResponse;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function statistik(Request $request)
    {
        $data = $this->dashboardService->statistik($request);

        return $this->successResponseWithData(
            $data,
            'Data statistik dashboard berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function wilayahSungai(Request $request)
    {
        $data = $this->dashboardService->wilayahSungai($request);

        return $this->successResponseWithData(
            $data,
            'Data statistik wilayah sungai berhasil diambil',
            Response::HTTP_OK
        );
    }

    #[QueryParameter('per_page', description: 'Jumlah aktivitas terbaru yang diambil.', type: 'int', default: 5, example: 5)]
    public function aktivitasTerbaru(Request $request)
    {
        $data = $this->dashboardService->aktivitasTerbaru($request);

        return $this->successResponseWithData(
            $data,
            'Data aktivitas terbaru berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function statistikTambahan(Request $request)
    {
        $data = $this->dashboardService->statistikTambahan($request);

        return $this->successResponseWithData(
            $data,
            'Data statistik tambahan berhasil diambil',
            Response::HTTP_OK
        );
    }
}
