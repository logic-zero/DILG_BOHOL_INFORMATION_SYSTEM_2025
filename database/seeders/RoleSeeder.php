<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $superAdmin = Role::firstOrCreate(['name' => 'Super-Admin']);
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'Publisher']);

        $permissions = ['manage users', 'view dashboard', 'manage news', 'add news'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['view dashboard', 'manage news', 'add news']);
        $userRole->givePermissionTo(['add news']);

        $superAdminUser = User::where('name', 'Developer')->first();

        if ($superAdminUser) {
            $superAdminUser->assignRole('Super-Admin');
        } else {
            echo "User with name 'Developer' not found.\n";
        }
    }
}

