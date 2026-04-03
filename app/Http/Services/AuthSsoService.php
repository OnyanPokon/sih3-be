<?php

namespace App\Http\Services;

use Exception;
use App\Exceptions\ServiceException;
use App\Models\Personil;
use App\Models\Unor;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
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

    public function loginCode($request)
    {
        try {

            $baseUri = rtrim(config('aptika_sso.base_uri_code'), '/');
            $clientId = config('aptika_sso.client_id_code');
            $clientSecret = config('aptika_sso.client_secret_code');
            $tokenPath = config('aptika_sso.token_path', '/oauth/token');
            $redirect_uri = config('aptika_sso.redirect_uri');
            $code = $request->input('code', "");

            if (!$baseUri || !$clientId || !$clientSecret || !$code || !$redirect_uri) {
                throw new Exception('Konfigurasi SSO belum lengkap', 500);
            }

            $client = new Client([
                'base_uri' => $baseUri,
                'timeout' => 10,
            ]);

            $response = $client->post($tokenPath, [
                'multipart' => [
                    [
                        'name' => 'grant_type',
                        'contents' => 'authorization_code',
                    ],
                    [
                        'name' => 'client_id',
                        'contents' => $clientId,
                    ],
                    [
                        'name' => 'client_secret',
                        'contents' => $clientSecret,
                    ],
                    [
                        'name' => 'redirect_uri',
                        'contents' => $redirect_uri,
                    ],
                    [
                        'name' => 'code',
                        'contents' => $code,
                    ]
                ],
                'http_errors' => false,
            ]);
            $statusCode = $response->getStatusCode();
            $body = json_decode((string)$response->getBody(), true);
            if ($statusCode !== 200 || !isset($body['access_token'])) {
                $message = $body['message'] ?? $body['error_description'] ?? 'Email atau password salah';
                throw new ConflictHttpException($message);
            }
            // return $body;
            $me = $this->fetchProfile($body['access_token'], 'code');
            // return $me;
            if (!$me) {
                throw new Exception('Gagal mengambil profil pengguna dari SSO (Profile)', 500);
            }
            DB::beginTransaction();

            $user = User::where('email', $me['email'])->first();

            if (!$user) {
                $unor = Unor::where('nama', $me['unorname'])->first();

                if (empty($me['unorname'])) {
                    throw new NotFoundHttpException('Unit organisasi belum diatur di SSO');
                }

                if (!$unor) {
                    throw new NotFoundHttpException('Unit organisasi tidak terdaftar di website SIH3');
                }

                $user = User::create([
                    'email' => $me['email'],
                    'username' => $me['nama'] ?? $me['email'],
                    'password' => Hash::make(uniqid()),
                    'telepon' => $me['nomor'] ?? null,
                    'is_default_password' => 0,
                    'is_verified' => 0,
                ]);

                Personil::create([
                    'user_id' => $user->id,
                    'nama' => $me['nama'],
                    'unor_id' => $unor->id,
                    'nip' => $me['nip'] ?? null,
                    'no_hp' => $me['nomor'] ?? null,
                    'status' => 'aktif',
                ]);

                $user->assignRole('admin');
            } else {
                $user->username = $me['nama'] ?? $me['email'];
                $user->save();
            }

            DB::commit();

            if (!$user) {
                throw new Exception('Gagal mengambil profil pengguna dari SSO', 500);
            }

            $expiry = Carbon::now()->addSeconds($body['expires_in'] ?? 3600);
            $cacheKey = "auth_token_user_{$user->id}";

            $cachedToken = Cache::get($cacheKey);
            if ($cachedToken) {
                $tokenModel = PersonalAccessToken::find($cachedToken['token_id'] ?? null);
                if (
                    $tokenModel &&
                    $tokenModel->tokenable_id === $user->id &&
                    $tokenModel->tokenable_type === get_class($user) &&
                    (!$tokenModel->expires_at || $tokenModel->expires_at->isFuture())
                ) {
                    return [
                        'token_type' => 'Bearer',
                        'token' => $cachedToken['token'],
                    ];
                }
                // Token di cache tidak valid lagi, bersihkan entri cache agar dibuat baru.
                Cache::forget($cacheKey);
            }

            $newToken = $user->createToken('auth_token', ['*'], $expiry);

            Cache::put($cacheKey, [
                'token_id' => $newToken->accessToken->id,
                'token' => $newToken->plainTextToken,
                'expires_at' => $expiry,
            ], $expiry);

            return [
                'token_type' => 'Bearer',
                'token' => $newToken->plainTextToken,
            ];
        } catch (GuzzleException $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw new Exception('Gagal menghubungi server SSO: ' . $e->getMessage(), 500, $e);
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    private function fetchProfile(string $accessToken,  $grantType = null): ?array
    {
        $baseUri = rtrim(config('aptika_sso.base_uri'), '/');
        if ($grantType !== null) {
            $baseUri = rtrim(config('aptika_sso.base_uri_code'), '/');
        }
        $pathUrl = config('aptika_sso.profile_path', '/api/v2/me');
        $profileUrl = $baseUri . $pathUrl;

        if (!$profileUrl) {
            return null;
        }

        try {
            $client = new Client([
                'timeout' => 10,
            ]);

            $response = $client->get($profileUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $profile = json_decode((string)$response->getBody(), true);
            if (is_array($profile)) {
                return $profile;
            }
            return null;
        } catch (GuzzleException $e) {
            return null;
        }
    }

    private function recordFailedLogin(string $attemptKey, string $blockKey): void
    {
        if (!Cache::has($attemptKey)) {
            Cache::put($attemptKey, 1, now()->addMinute());
            return;
        }

        $attempts = Cache::increment($attemptKey);

        if ($attempts >= 3) {
            Cache::put($blockKey, true, now()->addMinute());
            Cache::forget($attemptKey);
        }
    }

    public function updatOrCreateUser($profileSSo)
    {
        $isNew = false;
        $user = User::where('email', $profileSSo['email'])->first();
        if (!$user) {
            $user = User::create([
                'email' => $profileSSo['email'],
                'username' => $profileSSo['nama'] ?? $profileSSo['email'],
                'password' => Hash::make(uniqid()), // Password acak karena login menggunakan SSO
                'telepon' => $profileSSo['nomor'] ?? null,
                'is_default_password' => 0,
                'is_verified' => 0,
            ]);
            $isNew = true;
        } else {
            $user->username = $profileSSo['nama'] ?? $profileSSo['email'];
            // $isNew = true;
        }

        return $user;
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
