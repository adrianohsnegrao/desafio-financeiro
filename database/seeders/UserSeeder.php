<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Usuário Comum',
            'email' => 'common@test.com',
            'cpf' => '11111111111',
            'password' => bcrypt('password'),
            'type' => 'common',
            'balance' => 1000.00,
        ]);

        User::create([
            'name' => 'Lojista',
            'email' => 'merchant@test.com',
            'cpf' => '22222222222',
            'password' => bcrypt('password'),
            'type' => 'merchant',
            'balance' => 0,
        ]);
    }
}
