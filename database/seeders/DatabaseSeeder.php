<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Sender;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        User::create([
            $admin = 'name' => 'Sinklaus Patrezki Dae',
            'email' => 'tgaimgod@gmail.com',
            'password' => bcrypt('Erickdae2001'),
            'roles' => 'admin'
        ]);

        Sender::create([
            'name' => 'Erick Dae',
            'address' => 'Konogakure No 47',
            'phone' => '082222duaterus',
            'email' => 'tgaimgod@gmail.com'
        ]);
        Department::create([
            'name' => 'Kelurahan Danga'
        ]);
    }
}