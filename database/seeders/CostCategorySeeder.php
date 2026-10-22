<?php

namespace Database\Seeders;

use App\Models\CostCategory;
use Illuminate\Database\Seeder;

class CostCategorySeeder extends Seeder
{
    /**
     * Clinic expense categories, keyed by name so re-running only updates them.
     */
    public function run(): void
    {
        $categories = [
            'Rent & Utilities' => 'إيجار العيادة، الكهرباء، المياه، الغاز، الإنترنت',
            'Salaries & Staff' => 'رواتب الاستقبال، التمريض، المساعدين، المكافآت والعمل الإضافي',
            'Medical Supplies' => 'المستلزمات الطبية، المستهلكة، التعقيم، الأدوية المستخدمة داخل العيادة',
            'Equipment & Maintenance' => 'صيانة الأجهزة الطبية، إصلاحات العيادة، قطع الغيار، عقود الصيانة',
            'Software & Subscriptions' => 'اشتراكات البرامج، أنظمة الإدارة، الخدمات الرقمية',
            'Marketing & Advertising' => 'إعلانات السوشيال ميديا، التصميمات، المطبوعات، الحملات التسويقية',
            'General & Administrative' => 'النظافة، الضيافة، الأدوات المكتبية، الرسوم والتراخيص، المحاسبة والمصروفات المتنوعة',
        ];

        foreach ($categories as $name => $description) {
            CostCategory::updateOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
