<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Alimentação', 'Atividades', 'Compras', 'Encontros', 'Hospedagem', 'Lazer e Entretenimento', 'Transporte'];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category
            ]);
        }
    }
}
