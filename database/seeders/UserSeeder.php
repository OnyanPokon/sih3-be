<?php

namespace Database\Seeders;

use App\Models\Personil;
use App\Models\Unor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSuperAdmin = Role::where('name', 'superadmin')->first();

        $userBapppeda = User::create([
            'username' => 'superadmin',
            'email' => 'superadmin@app.id',
            'password' => bcrypt('password'),
        ]);

        $userBapppeda->assignRole($roleSuperAdmin);

        $bapppeda = Unor::create([
            'nama' => 'Bappeda',
            'wilayah' => 'Provinsi',
            'alamat' => 'Bappeda',
            'email' => 'bapppeda@app.id',
            'no_hp' => '08123456789',
        ]);

        $userBapppeda = Personil::create([
            'user_id' => $userBapppeda->id,
            'unor_id' => $bapppeda->id,
            'nip' => '123456789',
            'nama' => 'Bappeda',
            'no_hp' => '08123456789',
            'jabatan' => 'Operator',
            'status' => 'aktif',
        ]);

        Log::info('User Bapppeda berhasil di tambahkan');

        $roleAdmin = Role::where('name', 'admin')->first();

        $userEsdm = User::create([
            'username' => 'admin',
            'email' => 'admin@app.id',
            'password' => bcrypt('password'),
        ]);

        $userEsdm->assignRole($roleAdmin);

        $esdm = Unor::create([
            'nama' => 'Esdm',
            'wilayah' => 'Provinsi',
            'alamat' => 'Esdm',
            'email' => 'esdm@app.id',
            'no_hp' => '08123456789',
        ]);

        $userEsdm = Personil::create([
            'user_id' => $userEsdm->id,
            'unor_id' => $esdm->id,
            'nip' => '123456789',
            'nama' => 'Esdm',
            'no_hp' => '08123456789',
            'jabatan' => 'Operator',
            'status' => 'aktif',
        ]);

        Log::info('User Esdm berhasil di tambahkan');
    }
}
