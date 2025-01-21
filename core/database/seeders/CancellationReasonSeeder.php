<?php

namespace Database\Seeders;

use App\Models\CancellationReason;
use App\Models\Customers;
use App\Models\Providers;
use Illuminate\Database\Seeder;

class CancellationReasonSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Seed cancellation reasons for customers
        $customerReasons = [
            [
                'text_en' => 'Change of plans',
                'text_ar' => 'تغيير الخطط',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Found a better deal',
                'text_ar' => 'وجدت صفقة أفضل',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Unexpected circumstances',
                'text_ar' => 'ظروف غير متوقعة',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Sickness',
                'text_ar' => 'مرض',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Family emergency',
                'text_ar' => 'طارئ عائلي',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Travel restrictions',
                'text_ar' => 'قيود السفر',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Weather conditions',
                'text_ar' => 'الظروف الجوية',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Financial reasons',
                'text_ar' => 'أسباب مالية',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Unsatisfactory service',
                'text_ar' => 'خدمة غير مرضية',
                'role' => Customers::class,
            ],
            [
                'text_en' => 'Change in schedule',
                'text_ar' => 'تغيير في الجدول',
                'role' => Customers::class,
            ],
        ];

        // Seed cancellation reasons for providers
        $providerReasons = [
            [
                'text_en' => 'Unforeseen circumstances',
                'text_ar' => 'ظروف غير متوقعة',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Overbooked',
                'text_ar' => 'حجز زائد',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Unavailable resources',
                'text_ar' => 'موارد غير متاحة',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Technical issues',
                'text_ar' => 'مشاكل تقنية',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Staff shortage',
                'text_ar' => 'نقص في العمالة',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Maintenance work',
                'text_ar' => 'أعمال الصيانة',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Renovation',
                'text_ar' => 'تجديد',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Quality concerns',
                'text_ar' => 'مخاوف من الجودة',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Policy changes',
                'text_ar' => 'تغييرات في السياسات',
                'role' => Providers::class,
            ],
            [
                'text_en' => 'Personal reasons',
                'text_ar' => 'أسباب شخصية',
                'role' => Providers::class,
            ],
        ];

        foreach (array_merge($customerReasons, $providerReasons) as $reason) {
            CancellationReason::updateOrCreate([
                'text_en' => $reason['text_en'],
            ], $reason);
        }
    }
}
