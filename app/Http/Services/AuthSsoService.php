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
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthSsoService
{
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
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirect_uri,
                    'code' => $code,
                ],
                'http_errors' => false,
            ]);
            $statusCode = $response->getStatusCode();
            $bodyStr = (string)$response->getBody();
            $body = json_decode($bodyStr, true);
            if ($statusCode !== 200 || !isset($body['access_token'])) {
                \Illuminate\Support\Facades\Log::error('SSO Token Error: ' . $bodyStr);
                $message = $body['message'] ?? $body['error_description'] ?? 'Email atau password salah';
                throw new ConflictHttpException($message);
            }
            // return $body;
            $me = $this->fetchProfile($body['access_token'], 'code');
            // return $me;
            if (!$me) {
                throw new Exception('Gagal mengambil profil pengguna dari SSO (Profile)', 500);
            }
            Log::info('me', [$me]);
            $user = $this->updateOrCreateUser($me);
            if (!$user) {
                throw new Exception('Gagal mengambil profil pengguna dari SSO', 500);
            }

            if ($user->personil && $user->personil->status === 'nonaktif') {
                throw new ConflictHttpException('Akun Anda belum aktif. Silakan hubungi administrator.');
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
            throw new Exception('Gagal menghubungi server SSO: ' . $e->getMessage(), 500, $e);
        } catch (Exception $e) {
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

    public function updateOrCreateUser($profileSSo)
    {
        $unorName = isset($profileSSo['unorname']) ? trim($profileSSo['unorname']) : '';
        $unorId = null;

        if ($unorName !== '') {
            $unor = Unor::where('nama', $unorName)->first();
            if (!$unor) {
                $unor = Unor::create([
                    'nama' => $unorName,
                    'wilayah' => '-',
                    'status' => 'nonaktif',
                ]);
            }
            $unorId = $unor->id;
        }

        $isNew = false;
        $user = User::where('email', $profileSSo['email'])->first();

        $userData = [
            'email' => $profileSSo['email'],
            'username' => $profileSSo['nama'] ?? $profileSSo['email'],
        ];

        if (!$user) {
            $userData['password'] = Hash::make(uniqid());
            $user = User::create($userData);
            $isNew = true;

            $user->assignRole('admin');
        } else {
            $user->update($userData);
        }

        $personnel = Personil::where('user_id', $user->id)->first();
        if (!$personnel) {
            $personnel = new Personil();
            $personnel->user_id = $user->id;
            $personnel->status = 'nonaktif';
        } else if ($isNew) {
            $personnel->status = 'nonaktif';
        }

        $personnel->unor_id = $unorId;
        $personnel->nip = isset($profileSSo['nip']) ? trim($profileSSo['nip']) : null;
        $personnel->nama = $profileSSo['nama'] ?? $user->username;
        $personnel->no_hp = $profileSSo['nomor_telepon'] ?? ($profileSSo['nomor'] ?? null);
        $personnel->jabatan = $profileSSo['jabatan'] ?? null;
        $personnel->save();

        if ($isNew) {
            // event(new UserBaruSsoEvent($user));
        }

        return $user;
    }
}
