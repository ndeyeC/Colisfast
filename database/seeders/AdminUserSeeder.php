<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::Create(
            [
                'name' => 'Colisfast',
                'email' => 'colisfast@gmail.com',
                'password' => Hash::make('passer'), // change mot de passe après 
                'role' => 'admin',

                ]
            );
    }
}
