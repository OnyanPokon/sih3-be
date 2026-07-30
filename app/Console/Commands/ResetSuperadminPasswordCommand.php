<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetSuperadminPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'superadmin:reset-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengubah password semua user dengan role superadmin menjadi @Password26!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::role('superadmin')->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ditemukan user dengan role superadmin.');
            return 0;
        }

        foreach ($users as $user) {
            $user->password = Hash::make('@Password26!');
            $user->save();
            $this->info("Password untuk superadmin username '{$user->username}' (Email: {$user->email}) berhasil diubah menjadi @Password26!");
        }

        $this->info('Berhasil mengubah password semua user superadmin.');
        return 0;
    }
}
