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
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        Log::info('Role berhasil di tambahkan atau sudah ada');

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
            'lihat_wilayah_sungai'
        ];

        foreach ($permissions_superadmin as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleSuperAdmin = Role::where('name', 'superadmin')->first();
        if ($roleSuperAdmin) {
            $roleSuperAdmin->syncPermissions($permissions_superadmin);
        }

        Log::info('Permission berhasil di tambahkan/diperbarui dan dikaitkan dengan role superadmin');

        $permissions_admin = [
            'dashboard',
            'kelola_personil',
            'perbarui_unor',
            'perbarui_profil',
            'kelola_artikel',
            'kelola_berita',
            'kelola_pemetaan',
            'lihat_kategori',
            'kelola_wilayah_sungai'
        ];

        foreach ($permissions_admin as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roleAdmin = Role::where('name', 'admin')->first();
        if ($roleAdmin) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $roleAdmin->syncPermissions($permissions_admin);
        }

        Log::info('Permission berhasil di tambahkan/diperbarui dan dikaitkan dengan role admin');
    }
}
