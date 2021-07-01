<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Create user manual
        $user = new User();
        $user->created_at = Carbon::now();
        $user->uuid = $faker->uuid;
        $user->fullname = 'user';
        $user->username = 'user';
        $user->email = 'user@gmail.com';
        $user->phone = '0123456';
        $user->password = app('hash')->make('password');
        $user->save();

        // Create user random
        User::factory()->count(10)->create();
    }
}
