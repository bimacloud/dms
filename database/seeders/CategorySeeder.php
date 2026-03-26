<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Invoice', 'description' => 'Billing and payment documents'],
            ['name' => 'PKS', 'description' => 'Perjanjian Kerja Sama (Contracts)'],
            ['name' => 'Foto', 'description' => 'Progress photos and attachments'],
            ['name' => 'Legal', 'description' => 'Permits and legal documents'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
