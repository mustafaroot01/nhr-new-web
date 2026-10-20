<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Resources and the abilities available on each.
     */
    private array $resources = [
        'posts' => 'المقالات',
        'pages' => 'الصفحات',
        'categories' => 'التصنيفات',
        'media' => 'الوسائط',
        'menus' => 'القوائم',
        'contacts' => 'رسائل التواصل',
        'banners' => 'البانرات',
        'faqs' => 'الأسئلة الشائعة',
        'reports' => 'التقارير والإصدارات',
        'complaints' => 'الشكاوى والبلاغات',
        'settings' => 'الإعدادات',
        'users' => 'المستخدمين',
        'roles' => 'الأدوار والصلاحيات',
        'activity' => 'سجل النشاط',
        'login_logs' => 'سجل الدخول',
    ];

    private array $abilities = [
        'view' => 'عرض',
        'create' => 'إضافة',
        'update' => 'تعديل',
        'delete' => 'حذف',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $created = [];
        foreach ($this->resources as $resource => $label) {
            foreach (array_keys($this->abilities) as $ability) {
                $name = "{$ability}_{$resource}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $created[] = $name;
            }
        }

        // Special ability: publishing posts
        Permission::firstOrCreate(['name' => 'publish_posts', 'guard_name' => 'web']);
        $created[] = 'publish_posts';

        // admin gets everything
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // editor: manage content, no settings/users/roles, no delete of structural items
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions(array_merge(
            $this->abilitiesFor('posts'),
            $this->abilitiesFor('pages'),
            $this->abilitiesFor('banners'),
            $this->abilitiesFor('faqs'),
            $this->abilitiesFor('reports'),
            ['view_categories', 'create_categories', 'update_categories'],
            ['view_media', 'create_media', 'update_media', 'delete_media'],
            ['view_contacts', 'update_contacts'],
            ['view_complaints', 'update_complaints'],
            ['publish_posts'],
        ));

        // author: create & edit own content, cannot publish or delete
        $author = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $author->syncPermissions([
            'view_posts', 'create_posts', 'update_posts',
            'view_media', 'create_media',
            'view_categories',
        ]);

        $this->command->info('صلاحيات أُنشئت: ' . count($created));
        $this->command->info('أدوار: admin, editor, author');
    }

    private function abilitiesFor(string $resource): array
    {
        return array_map(fn ($a) => "{$a}_{$resource}", array_keys($this->abilities));
    }
}
