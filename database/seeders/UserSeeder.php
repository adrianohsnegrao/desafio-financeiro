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

        User::create([
            'name' => 'Usuário Comum 2',
            'email' => 'common2@test.com',
            'cpf' => '33333333333',
            'password' => bcrypt('password'),
            'type' => 'common',
            'balance' => 250.50,
        ]);

        User::create([
            'name' => 'Usuário Comum 3',
            'email' => 'common3@test.com',
            'cpf' => '44444444444',
            'password' => bcrypt('password'),
            'type' => 'common',
            'balance' => 500.75,
        ]);

        User::create([
            'name' => 'Lojista 2',
            'email' => 'merchant2@test.com',
            'cpf' => '55555555555',
            'password' => bcrypt('password'),
            'type' => 'merchant',
            'balance' => 10.00,
        ]);

        User::create([
            'name' => 'Lojista 3',
            'email' => 'merchant3@test.com',
            'cpf' => '66666666666',
            'password' => bcrypt('password'),
            'type' => 'merchant',
            'balance' => 75.25,
        ]);
    }
}
