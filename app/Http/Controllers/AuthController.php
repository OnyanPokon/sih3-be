<?php

namespace App\Http\Controllers;

use App\Http\Requests\GantiPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\MeResource;
use App\Http\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    use ApiResponse;
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request);
        return $this->successResponseWithData(
            $data,
            "Berhasil Login",
            Response::HTTP_OK
        );
    }

    public function me(Request $request)
    {

        $data = $this->authService->me($request);

        return $this->successResponseWithData(
            MeResource::make($data),
            "Berhasil mengambil data user",
            Response::HTTP_OK
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return $this->successResponse(
            "Berhasil logout",
            Response::HTTP_OK
        );
    }

    public function gantiPassword(GantiPasswordRequest $request)
    {
        $data = $this->authService->gantiPassword($request);
        $this->authService->logout($request);
        return $this->successResponseWithData(
            $data,
            "Berhasil mengganti password",
            Response::HTTP_OK
        );
    }
}
