<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class NameAssistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nameassist = [
            ['code' => 'AT', 'name' => 'RAM H'],
            ['code' => 'TO', 'name' => 'RAM H'],
            ['code' => 'BY', 'name' => 'RAM H'],
            ['code' => 'TOM', 'name' => 'RAM H'],
            ['code' => 'AF', 'name' => 'RAM H'],
            ['code' => 'NT', 'name' => 'RAM H'],
            ['code' => 'TB', 'name' => 'RAM H'],
            ['code' => 'X3', 'name' => 'RAM H'],
            ['code' => 'LG', 'name' => 'RAM H'],
            ['code' => 'DE', 'name' => 'RAM H'],
            ['code' => 'FR', 'name' => 'SPM'],
            ['code' => 'RK', 'name' => 'SPM'],
            ['code' => 'U2', 'name' => 'SPM'],
            ['code' => 'DS', 'name' => 'SPM'],
            ['code' => 'EC', 'name' => 'SPM'],
            ['code' => 'BA', 'name' => 'SPM'],
            ['code' => 'WK', 'name' => 'SPM'],
            ['code' => '3O', 'name' => 'SPM'],
            ['code' => 'EW', 'name' => 'SPM'],
            ['code' => 'LS', 'name' => 'SPM'],
            ['code' => 'V7', 'name' => 'SPM'],
            ['code' => 'MS', 'name' => 'SPM'],
            ['code' => 'TP', 'name' => 'SPM'],
        ];

        DB::table('name_assist')->insert($nameassist);
    }
}
