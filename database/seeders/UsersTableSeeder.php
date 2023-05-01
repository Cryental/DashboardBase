<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::factory()->count(100)->make()->toArray();

        foreach ($users as $user) {
            \App\Models\User::query()->create($user);
        }
    }
}
