<?php

namespace App\Http\Services;

use Exception;
use App\Models\User;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AuthService
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function me($request)
    {
        $data = $request->user()->load([
            'personil.unor.kategoris'
        ]);
        if (!$data) {
            throw new Exception('User tidak ditemukan', 404);
        }
        return $data;
    }

    public function login($request)
    {
        DB::beginTransaction();
        try {
            $validate = $request->validated();

            $credentials = filter_var($validate['email'], FILTER_VALIDATE_EMAIL)
                ? ['email' => $validate['email'], 'password' => $validate['password']]
                : ['username' => $validate['email'], 'password' => $validate['password']];


            if (!Auth::attempt($credentials)) {
                throw new ConflictHttpException('Email atau password salah');
            }

            $user = User::where('username', $validate['email'])->orWhere('email', $validate['email'])->first();

            $token = $user->createToken('auth_token')->plainTextToken;
            $data = [
                'token_type' => 'Bearer',
                'token' => $token,
            ];

            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function logout($request)
    {
        $data = $request->user()->currentAccessToken()->delete();

        return $data;
    }

    public function gantiPassword($request)
    {
        $data = $request->user();
        if (!Hash::check($request->password_lama, $data->password)) {
            throw new ConflictHttpException('Password lama salah');
        }
        $data->password = bcrypt($request->password_baru);
        $data->save();
        return $data;
    }
}
