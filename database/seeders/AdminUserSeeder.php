<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Wahid (Super Admin)',
                'email' => 'sadmin-wahid@pcmsimo.or.id',
                'password' => env('SEED_SUPERADMIN_PASSWORD', 'ChangeMe_SuperAdmin!2026'),
                'role' => 'superadmin',
            ],
            [
                'name' => 'Arul (Admin)',
                'email' => 'admin-arul@pcmsimo.or.id',
                'password' => env('SEED_ADMIN_PASSWORD', 'ChangeMe_Admin!2026'),
                'role' => 'admin',
            ],
            [
                'name' => 'Tim Redaksi',
                'email' => 'timredaksi@pcmsimo.or.id',
                'password' => env('SEED_TIM_PASSWORD', 'ChangeMe_Tim!2026'),
                'role' => 'tim',
            ],
        ];

        $validEmails = [];
        $superAdminUser = null;

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'password' => Hash::make($data['password']),
                ]
            );

            if ($data['role'] === 'superadmin') {
                $superAdminUser = $user;
            }

            $validEmails[] = $data['email'];
        }

        // Reassign any existing articles to Super Admin if authored by legacy users
        if ($superAdminUser) {
            Article::whereNotIn('user_id', User::whereIn('email', $validEmails)->pluck('id'))
                ->update(['user_id' => $superAdminUser->id]);
        }

        // Hapus semua pengguna selain 3 akun resmi ini
        User::whereNotIn('email', $validEmails)->delete();
    }
}
