<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namaRole = [
            'superadmin',
            'admin'
        ];

        foreach ($namaRole as $role) {
            Role::create(['name' => $role]);
        }

        Log::info('Role berhasil di tambahkan');

        $permissions_superadmin = [
            'dashboard',
            'kelola_kategori',
            'kelola_personil',
            'kelola_unor',
            'kelola_pengguna',
            'kelola_produk_hukum',
            'lihat_artikel',
            'lihat_pemetaan',
            'lihat_berita',
            'perbarui_profil',
            'kelola_produk_hukum'
        ];

        foreach ($permissions_superadmin as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleSuperAdmin = Role::where('name', 'superadmin')->first();

        foreach ($permissions_superadmin as $permission) {
            $roleSuperAdmin->givePermissionTo($permission);
        }

        Log::info('Permission berhasil di tambahkan dan dikaitkan dengan role superadmin');

        $permissions_admin = [
            'dashboard',
            'kelola_personil',
            'perbarui_unor',
            'perbarui_profil',
            'kelola_artikel',
            'kelola_berita',
            'kelola_pemetaan',
            'lihat_kategori'
        ];

        foreach ($permissions_admin as $permission) {
            if (Permission::where('name', $permission)->exists()) {
                continue;
            }
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        $roleAdmin = Role::where('name', 'admin')->first();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($permissions_admin as $permission) {
            $roleAdmin->givePermissionTo($permission);
        }

        Log::info('Permission berhasil di tambahkan dan dikaitkan dengan role admin');
    }
}
