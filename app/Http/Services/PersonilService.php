<?php

namespace App\Http\Services;

use Exception;
use App\Models\Personil;
use App\Exceptions\ServiceException;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonilService
{

    protected $model;

    public function __construct(Personil $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%')
                ->orWhere('jabatan', 'like', '%' . $search . '%')
                ->orWhere('nip', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }

        if ($unor_id = $request->query('unor_id')) {
            $data->where('unor_id', $unor_id);
        }

        return $data->paginate($per_page);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $user = User::create([
                'username' => $validatedData['nip'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['nip']),
            ]);

            $user->assignRole($validatedData['role']);

            $this->model->create([
                'user_id' => $user->id,
                'unor_id' => $validatedData['unor_id'],
                'nip' => $validatedData['nip'],
                'nama' => $validatedData['nama'],
            ]);

            DB::commit();

            return $validatedData;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }
        return $data;
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $data = $this->show($id);

            if ($validatedData['nip'] || $validatedData['email']) {
                $data->user->update([
                    'username' => $validatedData['nip'],
                    'email' => $validatedData['email'],
                ]);
            }

            if ($validatedData['role']) {
                $data->user->syncRoles($validatedData['role']);
            }

            $data->update([
                'unor_id' => $validatedData['unor_id'],
                'nip' => $validatedData['nip'],
                'nama' => $validatedData['nama'],
            ]);

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function updateProfil($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $data = $this->show($id);

            $userData = [];

            if (array_key_exists('nip', $validatedData)) {
                $userData['username'] = $validatedData['nip'];
            }
            if (array_key_exists('email', $validatedData)) {
                $userData['email'] = $validatedData['email'];
            }

            if (!empty($userData)) {
                $data->user->update($userData);
            }

            $personilData = Arr::except($validatedData, ['email']);

            $data->update($personilData);

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->show($id);
            $data->user->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function multiDestroy($ids)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->whereIn('id', explode(",", $ids))->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }

            $this->model->whereIn('id', explode(",", $ids))->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
