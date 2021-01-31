<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
           'first_name' => 'Tariqul',
           'last_name' => 'Islam',
            'username' => 'tariqulislamrc',
            'email' => 'tariqulislamrc@gmail.com',
            'status' => 'activated',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make(123456),
            'uuid' => Str::uuid()
        ]);

       Role::create([
           'name' => 'admin'
        ]);

        $user->assignRole('admin');
    }
}
