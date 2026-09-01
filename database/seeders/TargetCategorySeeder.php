<?php

namespace Database\Seeders;

use App\Models\TargetCategory;
use Illuminate\Database\Seeder;

class TargetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'الأطفال',
                'description' => 'الفئة المستهدفة من الأطفال (0-18 سنة)',
                'is_active' => true,
            ],
            [
                'name' => 'النساء',
                'description' => 'الفئة المستهدفة من النساء',
                'is_active' => true,
            ],
            [
                'name' => 'كبار السن',
                'description' => 'الفئة المستهدفة من كبار السن (60+ سنة)',
                'is_active' => true,
            ],
            [
                'name' => 'ذوي الاحتياجات الخاصة',
                'description' => 'الفئة المستهدفة من ذوي الاحتياجات الخاصة',
                'is_active' => true,
            ],
            [
                'name' => 'الشباب',
                'description' => 'الفئة المستهدفة من الشباب (18-35 سنة)',
                'is_active' => true,
            ],
            [
                'name' => 'الأسر الفقيرة',
                'description' => 'الفئة المستهدفة من الأسر ذات الدخل المحدود',
                'is_active' => true,
            ],
            [
                'name' => 'النازحين',
                'description' => 'الفئة المستهدفة من النازحين داخلياً',
                'is_active' => true,
            ],
            [
                'name' => 'اللاجئين',
                'description' => 'الفئة المستهدفة من اللاجئين',
                'is_active' => true,
            ],
            [
                'name' => 'المزارعين',
                'description' => 'الفئة المستهدفة من المزارعين وأصحاب الأراضي الزراعية',
                'is_active' => true,
            ],
            [
                'name' => 'أصحاب المشاريع الصغيرة',
                'description' => 'الفئة المستهدفة من أصحاب المشاريع الصغيرة والمتوسطة',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            TargetCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
