<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'      => 'Jāzeps Reinis',
            'email'     => 'jaazeps.bumbiers@gmail.com',
            'password'  => Hash::make('Z#i$RjHajCk9QTTO'),
        ]);
    }
}
