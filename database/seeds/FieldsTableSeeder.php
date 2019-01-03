<?php

use Illuminate\Database\Seeder;

class FieldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\Field::class, 20)->create()->each(function ($u) {
            for ($i = 0; $i < 7; $i++) {
                for ($j = 8; $j < 24; $j++) {
                    $u->profile()->save(factory(\App\Models\FieldProfile::class)->make(['weekday' => $i, 'time' => $j]));//生成场地信息数据
                }
            }
        });
    }
}
