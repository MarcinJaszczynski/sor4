<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdminUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleName = config('filament-shield.super_admin.name', 'super_admin');

        // Ensure role exists
        Role::firstOrCreate(['name' => $roleName]);

        // Create or update the user
        $user = User::updateOrCreate(
            ['email' => 'm.jaszczynski@gmail.com'],
            [
                'name' => 'Marcin Jaszczynski',
                'password' => Hash::make('12345678'),
                'status' => 'active',
                'type' => 'admin',
            ]
        );

        // Assign role if missing
        if (! $user->hasRole($roleName)) {
            $user->assignRole($roleName);
        }

        $this->command->info("User {$user->email} created/updated and assigned role {$roleName}.");
    }
}
