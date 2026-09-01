<?php

namespace Database\Seeders;

use App\Models\Association;
use App\Models\Donor;
use App\Models\FinancingForm;
use App\Models\FinancingType;
use App\Models\FundingSource;
use App\Models\SubFinancingForm;
use Illuminate\Database\Seeder;

class FinancialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Funding Sources
        $fundingSources = [
            'الحكومة المحلية',
            'المنظمات الدولية',
            'القطاع الخاص',
            'المنظمات غير الحكومية',
            'التمويل الذاتي',
        ];

        foreach ($fundingSources as $source) {
            FundingSource::firstOrCreate(['name' => $source]);
        }

        // Create Financing Types
        $financingTypes = [
            'منحة',
            'قرض',
            'استثمار',
            'شراكة',
            'تمويل مختلط',
        ];

        foreach ($financingTypes as $type) {
            FinancingType::firstOrCreate(['name' => $type]);
        }

        // Create Financing Forms
        $financingForms = [
            'تمويل كامل',
            'تمويل جزئي',
            'تمويل مشترك',
            'تمويل متدرج',
        ];

        foreach ($financingForms as $form) {
            FinancingForm::firstOrCreate(['name' => $form]);
        }

        // Create Sub Financing Forms
        $subFinancingForms = [
            ['name' => 'تمويل مقدم', 'financing_form_id' => 1],
            ['name' => 'تمويل مؤجل', 'financing_form_id' => 1],
            ['name' => 'تمويل على دفعات', 'financing_form_id' => 2],
            ['name' => 'تمويل حسب المراحل', 'financing_form_id' => 2],
        ];

        foreach ($subFinancingForms as $subForm) {
            SubFinancingForm::firstOrCreate($subForm);
        }

        // Create Donors
        $donors = [
            'البنك الدولي',
            'صندوق النقد الدولي',
            'الاتحاد الأوروبي',
            'الوكالة الأمريكية للتنمية الدولية',
            'صندوق التنمية السعودي',
        ];

        foreach ($donors as $donor) {
            Donor::firstOrCreate(['name' => $donor]);
        }

        // Create Associations
        $associations = [
            'جمعية التنمية المحلية',
            'جمعية رجال الأعمال',
            'الغرفة التجارية',
            'اتحاد المقاولين',
            'جمعية المهندسين',
        ];

        foreach ($associations as $association) {
            Association::firstOrCreate(['name' => $association]);
        }

        $this->command->info('تم إنشاء البيانات المالية بنجاح!');
    }
}
