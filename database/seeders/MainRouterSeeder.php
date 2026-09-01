<?php

namespace Database\Seeders;

use App\Models\MainRouter;
use App\Models\SubRouter;
use Illuminate\Database\Seeder;

class MainRouterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainRouters = [
            [
                'main_router' => 'التنمية الاقتصادية',
                'is_active' => true,
                'sub_routers' => [
                    'المشاريع الصغيرة والمتوسطة',
                    'التدريب المهني',
                    'الدعم المالي',
                    'ريادة الأعمال',
                ],
            ],
            [
                'main_router' => 'التنمية الاجتماعية',
                'is_active' => true,
                'sub_routers' => [
                    'الرعاية الصحية',
                    'التعليم والتدريب',
                    'الحماية الاجتماعية',
                    'تمكين المرأة',
                ],
            ],
            [
                'main_router' => 'البنية التحتية',
                'is_active' => true,
                'sub_routers' => [
                    'المياه والصرف الصحي',
                    'الطرق والمواصلات',
                    'الكهرباء والطاقة',
                    'الاتصالات',
                ],
            ],
            [
                'main_router' => 'الزراعة والأمن الغذائي',
                'is_active' => true,
                'sub_routers' => [
                    'الإنتاج الزراعي',
                    'الثروة الحيوانية',
                    'الأمن الغذائي',
                    'التسويق الزراعي',
                ],
            ],
            [
                'main_router' => 'البيئة والمناخ',
                'is_active' => true,
                'sub_routers' => [
                    'حماية البيئة',
                    'التكيف مع المناخ',
                    'الطاقة المتجددة',
                    'إدارة النفايات',
                ],
            ],
        ];

        foreach ($mainRouters as $routerData) {
            $mainRouter = MainRouter::create([
                'main_router' => $routerData['main_router'],
                'is_active' => $routerData['is_active'],
            ]);

            // Create sub routers
            foreach ($routerData['sub_routers'] as $subRouterName) {
                SubRouter::create([
                    'main_router_id' => $mainRouter->id,
                    'sub_router' => $subRouterName,
                    'is_active' => true,
                ]);
            }
        }
    }
}
