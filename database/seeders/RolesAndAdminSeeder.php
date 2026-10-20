<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);

        $user = User::create([
            'name' => 'مدير الموقع',
            'email' => 'admin@nahr-iq.org',
            'password' => bcrypt('Admin@2024!'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole($admin);

        $this->seedDefaultSettings();
    }

    private function seedDefaultSettings(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'site_name_ar', 'value' => 'جمعية النضال لحقوق الإنسان'],
            ['group' => 'general', 'key' => 'site_name_en', 'value' => 'Al-Nidal Association for Human Rights'],
            ['group' => 'general', 'key' => 'site_description_ar', 'value' => 'جمعية عراقية غير حكومية تعمل في مجال حقوق الإنسان'],
            ['group' => 'general', 'key' => 'site_description_en', 'value' => 'An Iraqi non-governmental organization working in the field of human rights'],
            ['group' => 'general', 'key' => 'logo', 'value' => null],
            ['group' => 'general', 'key' => 'favicon', 'value' => null],
            ['group' => 'theme', 'key' => 'primary_color', 'value' => '#1a5276'],
            ['group' => 'theme', 'key' => 'secondary_color', 'value' => '#2c3e50'],
            ['group' => 'theme', 'key' => 'accent_color', 'value' => '#c9941e'],
            ['group' => 'theme', 'key' => 'font_ar', 'value' => 'Tajawal'],
            ['group' => 'theme', 'key' => 'font_en', 'value' => 'Inter'],
            ['group' => 'social', 'key' => 'facebook', 'value' => ''],
            ['group' => 'social', 'key' => 'twitter', 'value' => ''],
            ['group' => 'social', 'key' => 'instagram', 'value' => ''],
            ['group' => 'social', 'key' => 'youtube', 'value' => ''],
            ['group' => 'social', 'key' => 'telegram', 'value' => ''],
            ['group' => 'seo', 'key' => 'meta_title_ar', 'value' => 'جمعية النضال لحقوق الإنسان'],
            ['group' => 'seo', 'key' => 'meta_title_en', 'value' => 'Al-Nidal Association for Human Rights'],
            ['group' => 'seo', 'key' => 'meta_description_ar', 'value' => 'جمعية عراقية غير حكومية تعمل في مجال حقوق الإنسان'],
            ['group' => 'seo', 'key' => 'meta_description_en', 'value' => 'An Iraqi non-governmental organization working in the field of human rights'],
            ['group' => 'seo', 'key' => 'google_analytics_id', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'footer_copyright_ar', 'value' => 'جميع الحقوق محفوظة © جمعية النضال لحقوق الإنسان'],
            ['group' => 'static_texts', 'key' => 'footer_copyright_en', 'value' => '© All Rights Reserved - Al-Nidal Association for Human Rights'],
            ['group' => 'static_texts', 'key' => 'contact_address_ar', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'contact_address_en', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'contact_phone', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'contact_email', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'contact_hours_ar', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'contact_hours_en', 'value' => ''],
            ['group' => 'static_texts', 'key' => 'google_maps_embed', 'value' => ''],
            ['group' => 'timeline', 'key' => 'items', 'value' => json_encode([
                ['year' => '2003', 'title_ar' => 'تأسيس الجمعية', 'title_en' => 'Foundation', 'desc_ar' => 'تأسست الجمعية في بغداد كمنظمة غير حكومية معنية بحقوق الإنسان', 'desc_en' => 'The association was founded in Baghdad as a human rights NGO'],
                ['year' => '2008', 'title_ar' => 'التوسع الإقليمي', 'title_en' => 'Regional Expansion', 'desc_ar' => 'توسعت أنشطة الجمعية لتشمل عدة محافظات عراقية', 'desc_en' => 'Activities expanded to cover multiple Iraqi governorates'],
                ['year' => '2013', 'title_ar' => 'الاعتراف الدولي', 'title_en' => 'International Recognition', 'desc_ar' => 'حصلت الجمعية على صفة استشارية لدى الأمم المتحدة', 'desc_en' => 'The association gained consultative status with the United Nations'],
                ['year' => '2018', 'title_ar' => 'إطلاق التقارير السنوية', 'title_en' => 'Annual Reports Launch', 'desc_ar' => 'بدأت الجمعية بإصدار تقارير سنوية شاملة عن أوضاع حقوق الإنسان', 'desc_en' => 'Began publishing comprehensive annual human rights reports'],
                ['year' => '2024', 'title_ar' => 'التحول الرقمي', 'title_en' => 'Digital Transformation', 'desc_ar' => 'أطلقت الجمعية منصتها الرقمية الجديدة لتعزيز الشفافية والتواصل', 'desc_en' => 'Launched a new digital platform to enhance transparency and communication'],
            ])],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
