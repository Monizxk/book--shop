<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Створення категорій для мов
        $english = Category::create(['name' => 'Англійська']);
        $french = Category::create(['name' => 'Французька']);
        $german = Category::create(['name' => 'Німецька']);
        
        // Створення підкатегорій для кожної мови
        $edition1_english = Category::create(['name' => 'Oxford', 'parent_id' => $english->id]);
        $edition2_english = Category::create(['name' => 'Penguin', 'parent_id' => $english->id]);
    
        $edition1_french = Category::create(['name' => 'Oxford', 'parent_id' => $french->id]);
        $edition2_french = Category::create(['name' => 'Penguin', 'parent_id' => $french->id]);
    
        $edition1_german = Category::create(['name' => 'Oxford', 'parent_id' => $german->id]);
        $edition2_german = Category::create(['name' => 'Penguin', 'parent_id' => $german->id]);
        
        // Створення підкатегорій для кожної з підкатегорій (книги)
        Category::create(['name' => 'English Grammar in Use', 'parent_id' => $edition1_english->id]);
        Category::create(['name' => 'Advanced English', 'parent_id' => $edition2_english->id]);
        Category::create(['name' => 'Advanced French', 'parent_id' => $edition2_french->id]);
        Category::create(['name' => 'French Grammar in Use', 'parent_id' => $edition1_french->id]);
    
        Category::create(['name' => 'German Grammar in Use', 'parent_id' => $edition1_german->id]);
        Category::create(['name' => 'Advanced German', 'parent_id' => $edition2_german->id]);
    }
}
