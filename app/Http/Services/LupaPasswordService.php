<?php

namespace App\Http\Services;

use Exception;
use App\Mail\VerifikasiPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LupaPasswordService
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function sendEmail($request)
    {
        try {
            $user = $this->model->where('email', $request->email)->first();
            if (!$user) {
                throw new NotFoundHttpException('Email tidak ditemukan');
            }

            $token = Str()->random(60);
            $user->remember_token = $token;
            $user->save();

            Mail::to($user->email)->send(new VerifikasiPasswordMail($user));

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function changePassword($request)
    {
        try {
            $validatedData = $request->validated();

            $user = $this->model->where('remember_token', $validatedData['token'])->first();
            if (!$user) {
                throw new NotFoundHttpException('Token tidak ditemukan');
            }
            $user->password = bcrypt($validatedData['password_baru']);
            $user->remember_token = null;
            $user->save();

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
