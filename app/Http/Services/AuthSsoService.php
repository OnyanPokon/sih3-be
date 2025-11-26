<?php

namespace App\Http\Services;

use Exception;
use App\Exceptions\ServiceException;
use App\Models\Personil;
use App\Models\Unor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthSsoService
{
    protected $model;
    private $apiUrl;
    protected $accessToken;


    public function __construct(User $model)
    {
        $this->model = $model;
        $this->apiUrl = env('SSO_URL');
        $this->accessToken = null;
    }

    private function connectSso($credentials)
    {
        $payload = [
            'grant_type' => 'password',
            'client_id' => env('SSO_CLIENT_ID'),
            'client_secret' => env('SSO_CLIENT_SECRET'),
            'username' => $credentials['email'],
            'password' => $credentials['password'],
        ];
        $url = $this->apiUrl . '/api/v2/oauth/token';
        try {
            $response = Http::post($url, $payload);

            if (!$response->successful()) {
                throw new \Exception($response->json()['message'] ?? 'Gagal terhubung ke SSO.');
            }

            $this->accessToken = $response->json()['access_token'];

            return $response->json()['access_token'];
        } catch (Exception $e) {
            throw new ServiceException('Gagal terhubung ke SSO: ' . $e->getMessage(), 500);
        }
    }

    private function getSsoUserData()
    {
        $url = $this->apiUrl . '/api/v2/me';
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($url);

            if (!$response->successful()) {
                throw new \Exception('Gagal mengambil data pengguna dari SSO.');
            }

            return $response->json();
        } catch (Exception $e) {
            throw new ServiceException('Gagal mengambil data pengguna dari SSO: ' . $e->getMessage(), 500);
        }
    }

    public function me($request)
    {
        $data = $request->user();
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

            $connectSso = $this->connectSso($credentials);

            if ($connectSso) {
                $ssoUserData = $this->getSsoUserData();

                $user = User::where('username', $validate['email'])->orWhere('email', $validate['email'])->first();

                if ($user) {
                    // kondisi ketika email atau username sudah terdaftar didatabase lokal
                    $token = $user->createToken('auth_token')->plainTextToken;
                    $data = [
                        'token_type' => 'Bearer',
                        'token' => $token,
                    ];
                } else {
                    // cek apakah unor sudah terdaftar atau tidak
                    $unor = Unor::where('nama', $ssoUserData['unorname'])->first();

                    if ($ssoUserData['unorname'] == null || $ssoUserData['unorname'] == '') {
                        throw new NotFoundHttpException('Unit organisasi belum diatur di SSO');
                    }

                    if (!$unor) {
                        throw new NotFoundHttpException('Unit organisasi tidak terdaftar di website SIH3');
                    }

                    // buat user baru
                    $newUser = User::create([
                        'username' => $ssoUserData['nik'],
                        'email' => $ssoUserData['email'],
                        'password' => bcrypt($validate['password']),
                    ]);

                    // buat personil baru huhuy
                    $newPersonil = Personil::create([
                        'user_id' => $newUser->id,
                        'nama' => $ssoUserData['nama'],
                        'unor_id' => $unor->id,
                        'nip' => $ssoUserData['nip'] ?? null,
                        'no_hp' => $ssoUserData['nomor'] ?? null,
                        'status' => 'aktif',
                    ]);

                    $newUser->assignRole('admin');

                    $token = $newUser->createToken('auth_token')->plainTextToken;
                    $data = [
                        'token_type' => 'Bearer',
                        'token' => $token,
                    ];
                }
            }

            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
