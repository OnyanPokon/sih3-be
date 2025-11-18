<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LupaPassword\GantiPasswordRequest;
use App\Http\Requests\LupaPassword\KirimEmailRequest;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\LupaPasswordService;
use App\Http\Resources\LupaPasswordResource;


class LupaPasswordController extends Controller
{
    use ApiResponse;

    protected $lupaPasswordService;

    public function __construct(LupaPasswordService $lupaPasswordService)
    {
        $this->lupaPasswordService = $lupaPasswordService;
    }

    public function sendEmail(KirimEmailRequest $request)
    {
        $this->lupaPasswordService->sendEmail($request);

        return $this->successResponse(
            'Email berhasil dikirim',
            Response::HTTP_OK
        );
    }

    public function changePassword(GantiPasswordRequest $request)
    {
        $this->lupaPasswordService->changePassword($request);

        return $this->successResponse(
            'Password berhasil diubah',
            Response::HTTP_CREATED
        );
    }
}
