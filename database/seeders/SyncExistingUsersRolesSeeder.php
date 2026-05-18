<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SyncExistingUsersRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->whereNotNull('role_id')->get();

        foreach ($users as $user) {
            try {
                $role = Role::findById((int) $user->role_id, 'web');
                $user->syncRoles([$role->name]);
            } catch (\Throwable $e) {
                // Skip users with invalid role_id to avoid stopping full sync.
                continue;
            }
        }
    }
}
