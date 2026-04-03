<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\MeResource;
use App\Http\Services\AuthSsoService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthSsoController extends Controller
{
    use ApiResponse;
    protected $authService;
    public function __construct(AuthSsoService $authService)
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

    public function loginSsoCallback(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:400',
        ]);
        $data = $this->authService->loginCode($request);
        return $this->successResponseWithData(
            $data,
            "Berhasil Login",
            Response::HTTP_OK
        );
    }
}
