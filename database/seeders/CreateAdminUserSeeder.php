<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['guard_name' => 'web']
        );

        $user = User::create([
            'user_type_id' => '1',
            'username' => 'dcim',
            'fullname' => 'DCIM Admin',
            'mobile' => '018162345',
            'email' => 'admin@gmail.com',
            'dept_id' => '1',
            'role_id' => $role->id,
            'password' => bcrypt('123456'),
            'status' => '1',
            'is_email' => '1',
            'is_sms' => '1',
            'password_change' => '1',
        ]);

        $permissions = Permission::pluck('id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole($role);
    }
}
