<?php

namespace Database\Seeders;

// use App\Models\Entity;
// use App\Models\EntityType;
use App\Models\FinancialItem;
// use App\Models\Supervisor; // Table doesn't exist
// use App\Models\Executor; // Table doesn't exist
// use App\Models\Participant; // Table doesn't exist
use Illuminate\Database\Seeder;

class EntityDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create additional Entity Types
        $entityTypes = [
            'شركة حكومية',
            'شركة خاصة',
            'مؤسسة غير ربحية',
            'جامعة',
            'مركز بحثي',
            'مستشفى',
            'مدرسة',
        ];

        /*
        foreach ($entityTypes as $type) {
            EntityType::firstOrCreate(['name' => $type]);
        }
        */

        // Create additional Entities
        $entities = [
            ['name' => 'وزارة التنمية', 'entity_type_id' => 1],
            ['name' => 'شركة الاستشارات التقنية', 'entity_type_id' => 2],
            ['name' => 'مؤسسة التنمية المجتمعية', 'entity_type_id' => 3],
            ['name' => 'جامعة الملك سعود', 'entity_type_id' => 4],
            ['name' => 'مركز الأبحاث التطبيقية', 'entity_type_id' => 5],
        ];

        /*
        foreach ($entities as $entity) {
            Entity::firstOrCreate($entity);
        }
        */

        // Create Supervisors - Commented out as table doesn't exist
        // $supervisors = [
        //     'د. أحمد محمد',
        //     'م. فاطمة علي',
        //     'أ. محمد سالم',
        //     'د. نورا أحمد',
        //     'م. خالد عبدالله'
        // ];

        // foreach ($supervisors as $supervisor) {
        //     Supervisor::firstOrCreate(['name' => $supervisor]);
        // }

        // Create Executors - Commented out as table doesn't exist
        // $executors = [
        //     'فريق التطوير الأول',
        //     'فريق التنفيذ الميداني',
        //     'فريق الدعم التقني',
        //     'فريق المتابعة والتقييم',
        //     'فريق التدريب'
        // ];

        // foreach ($executors as $executor) {
        //     Executor::firstOrCreate(['name' => $executor]);
        // }

        // Create Participants - Commented out as table doesn't exist
        // $participants = [
        //     'المستفيدون المباشرون',
        //     'المجتمع المحلي',
        //     'الجهات الحكومية',
        //     'القطاع الخاص',
        //     'المنظمات الشريكة'
        // ];

        // foreach ($participants as $participant) {
        //     Participant::firstOrCreate(['name' => $participant]);
        // }

        // Note: Procedures model doesn't exist, skipping procedures seeding

        // Create Financial Items
        $financialItems = [
            ['code' => '001', 'name' => 'رواتب ومكافآت'],
            ['code' => '002', 'name' => 'مواد وأدوات'],
            ['code' => '003', 'name' => 'خدمات استشارية'],
            ['code' => '004', 'name' => 'تدريب وتطوير'],
            ['code' => '005', 'name' => 'سفر وانتقالات'],
            ['code' => '006', 'name' => 'معدات وأجهزة'],
            ['code' => '007', 'name' => 'إيجارات'],
            ['code' => '008', 'name' => 'مصاريف إدارية'],
        ];

        foreach ($financialItems as $item) {
            FinancialItem::firstOrCreate(['code' => $item['code']], $item);
        }

        $this->command->info('تم إنشاء بيانات الجهات والموارد بنجاح!');
    }
}
