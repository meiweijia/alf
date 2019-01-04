<?php

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
        factory(\App\Models\User::class, 10)->create()->each(function ($u) {
            $u->profile()->save(factory(\App\Models\UserProfile::class)->make());//生成用户信息数据
        });
    }
}
