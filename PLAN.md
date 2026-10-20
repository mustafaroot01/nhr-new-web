# خطة بناء موقع جمعية النضال لحقوق الإنسان - Laravel + Inertia + Vue 3

## السياق
استبدال موقع WordPress الحالي (nahr-iq.org) بموقع جديد مبني على Laravel 11 + Inertia.js + Vue 3 (مشروع واحد، بيلد واحد). لوحة التحكم Filament v3. الموقع يخدم جمعية حقوق إنسان عراقية ويحتاج نشر بلغتين (عربي/إنكليزي) مع محرر غني ورفع ملفات متعددة.

## البيانات الموجودة للنقل
- **136 مقال** منشور (HTML كلاسيكي)
- **8 صفحات** (4 عربي + 4 إنكليزي مترجمة)
- **7 تصنيفات** (مع تفرعات وترجمات)
- **~1,500 ملف وسائط** (صور + PDF)
- **2 مستخدمين** (أدمن + محرر)
- **4 قوائم تنقل** (رئيسية + فوتر × عربي/إنكليزي)
- **نموذج اتصال واحد** (اسم، إيميل، موضوع، رسالة)

---

## 1. هيكل المشروع (مشروع واحد)

```
/Users/root1/Downloads/test/newweb/
├── app/
│   ├── Filament/Resources/     # لوحة تحكم Filament
│   ├── Http/
│   │   ├── Controllers/        # Inertia controllers
│   │   ├── Middleware/          # Auth, Locale, Security
│   │   └── Requests/           # Form validation
│   ├── Models/
│   ├── Policies/
│   └── Services/
├── database/migrations/
├── resources/
│   ├── js/
│   │   ├── Pages/              # صفحات Vue.js
│   │   ├── Components/         # مكونات Vue.js
│   │   ├── Layouts/            # القالب الرئيسي
│   │   └── Composables/        # منطق مشترك
│   ├── css/app.css             # Tailwind
│   └── lang/                   # ar.json, en.json
├── routes/
│   ├── web.php                 # راوتات الموقع (Inertia)
│   └── admin.php               # Filament تلقائي
├── storage/app/public/media/
├── migration/                  # سكربتات نقل البيانات من WordPress
│   └── migrate.php
└── vite.config.js
```

**القرارات التقنية:**
- **Laravel 11 + Inertia.js + Vue 3** - مشروع واحد، بيلد واحد، deploy واحد
- **Filament v3** للداشبورد - جاهز RTL، محرر غني، رفع ملفات
- **Vite** للـ build - سريع، يدعم HMR
- **لا حاجة لـ Node.js بالسيرفر** - Vite يبني ملفات ثابتة، Laravel يخدمها

---

## 2. قاعدة البيانات (13 جدول)

### الجداول الرئيسية:

**`posts`** - المقالات والصفحات
- id, type (post/page), slug, featured_image_id, author_id, category_id, status (draft/published/archived), is_featured, view_count, published_at, deleted_at

**`post_translations`** - ترجمات المحتوى
- post_id, locale (ar/en), title, subtitle, excerpt, body (HTML), meta_title, meta_description
- UNIQUE(post_id, locale)

**`categories`** - التصنيفات (هرمية)
- id, parent_id, slug, sort_order, **is_visible** (إخفاء/إظهار), deleted_at

**`category_translations`** - ترجمات التصنيفات
- category_id, locale, name, description

**`media`** - الوسائط
- id, path, filename, original_name, mime_type, size, width, height, alt_text, uploaded_by

**`post_media`** - صور متعددة ومرفقات لكل مقال
- post_id, media_id, type (gallery/attachment), sort_order

**`menus` + `menu_items`** - القوائم
- menus: location (primary/footer), locale (ar/en)
- menu_items: menu_id, parent_id, label, type (page/category/url), reference_id, **is_visible**, sort_order

**`contact_submissions`** - رسائل التواصل
- name, email, phone, subject, message, is_read, is_archived, ip_address, locale, created_at
- الرسائل تصل مباشرة للوحة التحكم مع إشعار (badge عدد غير المقروءة)
- إمكانية الرد على الإيميل مباشرة من لوحة التحكم

**`settings`** - إعدادات الموقع (key-value)
- group, key, value
- **general**: اسم الموقع (ar/en)، وصف الموقع (ar/en)، لوقو، favicon
- **theme**: اللون الرئيسي، اللون الثانوي، لون التمييز، الخط العربي، الخط الإنكليزي
- **social**: فيسبوك، تويتر، انستغرام، يوتيوب، تلغرام
- **seo**: meta title، meta description، google analytics ID
- **static_texts**: كل النصوص الثابتة بالموقع قابلة للتعديل من لوحة التحكم (بالعربي والإنكليزي):
  - نصوص الهيدر والفوتر (حقوق النشر، عنوان الجمعية، رقم الهاتف، الإيميل)
  - نصوص الصفحة الرئيسية (عنوان السلايدر، وصف الجمعية، عناوين الأقسام)
  - نصوص صفحة "من نحن" (الرؤية، الرسالة، الأهداف)
  - نصوص صفحة "اتصل بنا" (العنوان، الهاتف، الإيميل، ساعات العمل، خريطة Google)
  - نصوص الأزرار ("اقرأ المزيد"، "عرض الكل"، "إرسال"، "طباعة"...)
  - رسائل النظام ("تم الإرسال بنجاح"، "لا توجد نتائج"...)
- يعني **لا شي ثابت بالكود** - كل نص يظهر للزائر يتغير من لوحة التحكم

**`users` + roles/permissions** - عبر Spatie laravel-permission
- دورين: admin (كل شي) و editor (إدارة المحتوى فقط)

---

## 3. الباك إند (Laravel)

### الحزم:
- Laravel 11، Filament v3، Spatie Permission، Laravel Sanctum
- Intervention/Image (تحسين الصور)، Mews/Purifier (تنظيف HTML)

### لوحة التحكم (Filament) - `nahr-iq.org/admin`:
- **إدارة المقالات**: تبويبات عربي/إنكليزي جنب بعض، محرر TipTap (يدعم كود HTML)، رفع صور متعددة، مرفقات PDF/Word
- **إدارة التصنيفات**: عرض شجري، سحب وإفلات للترتيب، زر إخفاء/إظهار
- **إدارة الوسائط**: رفع جماعي، معاينة، تصفية حسب النوع
- **إدارة القوائم**: بناء قائمة متداخلة
- **رسائل التواصل**: جدول مع حالة مقروء/غير مقروء، عدد الرسائل الجديدة بالشريط الجانبي (badge)، تفاصيل كل رسالة (اسم المرسل، إيميله، الموضوع، النص الكامل، التاريخ، IP)، زر رد سريع بالإيميل، إمكانية حذف/أرشفة
- **الإعدادات**: اسم الموقع، لوقو، الألوان، سوشيال ميديا، SEO

### هيكلية الكود (مهم جداً):

**API Resources (app/Http/Resources/)** - تنسيق البيانات المرسلة:
```
PostResource          → id, slug, title, excerpt, featured_image, category, author, view_count, published_at
PostDetailResource    → كل ما فوق + body, gallery, attachments, related_posts, translations
CategoryResource      → id, slug, name, description, parent, children_count, is_visible
MediaResource         → id, url, thumbnail_url, filename, mime_type, size
MenuResource          → id, location, items (nested)
ContactResource       → id, name, email, phone, subject, message, is_read, created_at
SettingResource       → group, key, value
```
كل Resource يرجع بيانات نظيفة ومنظمة - لا بيانات زائدة، لا حقول حساسة

**Form Requests (app/Http/Requests/)** - تحقق من المدخلات:
```
StorePostRequest      → title (required, max:255), body (required), locale, category_id, status
UpdatePostRequest     → نفس الحقول لكن optional
StoreCategoryRequest  → name (required), slug (unique), parent_id (exists), is_visible
ContactFormRequest    → name (required), email (required|email), phone, subject (required), message (required, max:5000), recaptcha
SearchRequest         → q (required, min:2), locale, category_id, page
```
كل request فيه rules() + messages() بالعربي والإنكليزي + authorize()

**API Response موحد (app/Traits/ApiResponse.php)**:
```php
// كل الاستجابات بنفس الشكل:
{
    "success": true,
    "data": { ... },           // البيانات (Resource أو Collection)
    "message": "تم بنجاح",
    "meta": {                  // للقوائم فقط
        "current_page": 1,
        "total": 136,
        "per_page": 12,
        "last_page": 12
    }
}

// عند الخطأ:
{
    "success": false,
    "message": "بيانات غير صحيحة",
    "errors": {
        "email": ["حقل الإيميل مطلوب"],
        "name": ["حقل الاسم مطلوب"]
    }
}
```
- كل controller يستخدم هذا الـ trait
- HTTP status codes صحيحة (200, 201, 422, 404, 500)
- رسائل خطأ واضحة بالعربي والإنكليزي حسب اللغة
- Pagination موحد على كل القوائم

**ملاحظة:** Inertia.js يمرر البيانات عبر Resources مباشرة من Controller إلى Vue - لا حاجة لـ API routes منفصلة للفرونت، لكن الـ Resources و Requests و ApiResponse trait جاهزين لو احتجنا نفتح API خارجي مستقبلاً (تطبيق موبايل مثلاً).

---

## 4. الفرونت إند (Vue 3 + Inertia)

### الحزم:
- Vue 3، Inertia.js، Tailwind CSS 3، HeadlessUI/Vue، vue-i18n

### الصفحات (resources/js/Pages/):
```
/ar                     → الصفحة الرئيسية (سلايدر + أقسام حسب التصنيف)
/en                     → English Homepage
/ar/posts               → كل المقالات
/ar/posts/{slug}        → مقال واحد (مع معرض صور ومرفقات)
/ar/category/{slug}     → مقالات تصنيف معين
/ar/about               → من نحن
/ar/contact             → اتصل بنا
/ar/privacy             → سياسة الخصوصية
/ar/media               → المكتبة الإعلامية (صور + فيديو)
/ar/search              → نتائج البحث
(نفس الشي لـ /en/...)
```

### المكونات الرئيسية (resources/js/Components/):
- **Layout**: AppLayout, AppHeader, AppFooter, MobileMenu, LanguageSwitcher, Breadcrumb
- **Home**: HeroBanner (سلايدر), CategorySection, LatestPosts
- **Post**: PostCard, PostContent (HTML renderer), PostGallery (lightbox), PostAttachments (PDF/Word downloads), RelatedPosts, ShareButtons
- **Category**: CategoryTree (sidebar), CategoryHeader
- **Media**: MediaPage (عرض كل الصور والفيديوهات بشكل grid)، MediaGallery (فلترة حسب النوع/التصنيف)، MediaLightbox
- **Contact**: ContactForm (اسم، إيميل، هاتف، موضوع، رسالة) مع validation + reCAPTCHA + رسالة نجاح

### RTL/LTR:
- CSS logical properties (ps-4 بدل pl-4)
- تبديل dir و lang على `<html>` حسب اللغة عبر Inertia middleware

### الخطوط:
- **الخط الافتراضي العربي**: Tajawal (تجوال) من Google Fonts
- **الخط الافتراضي الإنكليزي**: Inter من Google Fonts
- **قابل للتغيير من لوحة التحكم**: الأدمن يقدر يستبدل الخط (مثلاً Cairo بدل Tajawal) من الإعدادات بدون تعديل كود
- **الخطوط المتاحة للاختيار**: Tajawal، Cairo، Almarai، Noto Kufi Arabic، IBM Plex Sans Arabic
- يتم تحميل الخط المختار ديناميكياً من Google Fonts

### صفحة المكتبة الإعلامية (MediaIndex.vue):
- **عرض Grid**: صور وفيديوهات بشكل شبكي متناسق (masonry grid)
- **فلترة**: حسب النوع (صور / فيديو / الكل) + حسب التصنيف + حسب السنة
- **Lightbox**: عند الضغط على صورة تفتح بحجم كامل مع تنقل بين الصور
- **ربط بالمقال**: كل صورة/فيديو تحتها رابط المقال اللي تنتمي إله → الضغط يوديك للمقال
- **Lazy Loading**: الصور تُحمّل تدريجياً عند التمرير (infinite scroll أو pagination)

### قسم الميديا بالصفحة الرئيسية:
- **آخر الصور**: شريط أو grid يعرض آخر 6-8 صور من المقالات
- **كل صورة**: thumbnail + عنوان المقال المرتبط → الضغط يفتح المقال
- **زر "عرض الكل"**: يوديك لصفحة المكتبة الإعلامية الكاملة

### Open Graph / مشاركة الروابط (مهم جداً):
عند مشاركة رابط مقال بفيسبوك/تويتر/واتساب/تلغرام يظهر:
- **og:title** → عنوان المقال
- **og:description** → المقتطف (excerpt)
- **og:image** → الصورة الرئيسية للمقال (بحجم 1200x630)
- **og:url** → رابط المقال الكامل
- **og:locale** → ar_AR أو en_US
- **og:site_name** → اسم الموقع
- **twitter:card** → summary_large_image
- يتم توليدها تلقائياً من بيانات المقال عبر `<Head>` component

### الهوية البصرية (من إعدادات لوحة التحكم):
- **الألوان**: تُسحب من جدول `settings` (primary, secondary, accent) → CSS variables
- **الشعار**: يُرفع من لوحة التحكم → يظهر بالهيدر والفوتر
- **اسم الموقع + الوصف**: من الإعدادات → الهيدر + SEO
- **أيقونات السوشيال ميديا**: روابط من الإعدادات → الفوتر
- يعني الأدمن يقدر يغير الألوان والشعار من لوحة التحكم بدون ما يلمس كود

### صفحة عرض المقال (PostShow.vue):
- **الهيدر**: عنوان المقال + التصنيف + اسم الكاتب + تاريخ النشر
- **الصورة الرئيسية**: عرض كامل
- **شريط أدوات القارئ**:
  - 🔍 تكبير/تصغير الخط (A+ / A-)، يحفظ التفضيل بالمتصفح
  - 🖨️ زر طباعة (يفتح نافذة طباعة مع تنسيق خاص للطباعة)
  - 👁️ عدد المشاهدات (يزيد تلقائياً عند فتح المقال)
  - 📤 أزرار مشاركة (فيسبوك، تويتر، واتساب، نسخ الرابط)
- **المحتوى**: HTML منسق مع دعم صور وفيديو مضمن
- **معرض الصور**: عرض شبكي + lightbox عند الضغط
- **المرفقات**: قائمة ملفات PDF/Word قابلة للتحميل مع أيقونة النوع والحجم
- **مقالات ذات صلة**: 3-4 مقالات من نفس التصنيف

### Responsive (متجاوب - مهم جداً):
- **Mobile First**: التصميم يبدأ من الموبايل ويتوسع للشاشات الكبيرة
- **Breakpoints**: sm(640px), md(768px), lg(1024px), xl(1280px)
- **الهيدر**: قائمة hamburger على الموبايل، قائمة كاملة على الديسكتوب
- **الصور**: تتكيف مع حجم الشاشة (`srcset` + `sizes`)
- **المقالات**: عمود واحد موبايل، 2 أعمدة تابلت، 3 أعمدة ديسكتوب
- **شريط الأدوات**: ثابت أسفل الشاشة على الموبايل، جانبي على الديسكتوب
- **الجداول**: قابلة للتمرير أفقياً على الموبايل
- **الخطوط**: حجم يتكيف مع الشاشة (clamp)

### الأداء وسرعة الموقع (مهم جداً):
- **Lazy Loading**: الصور تُحمّل فقط عند الظهور بالشاشة (`loading="lazy"`)
- **Image Optimization**: تحويل الصور لـ WebP تلقائياً + أحجام متعددة (thumbnail, medium, large) عبر Intervention/Image
- **Cache**: تخزين مؤقت للصفحات والبيانات عبر Laravel Cache (Redis/File)
- **Route Model Binding + Eager Loading**: لا N+1 queries - كل الـ relations تُحمّل مرة واحدة
- **Minification**: CSS + JS مضغوطة ومدمجة عبر Vite (production build)
- **CDN Ready**: الملفات الثابتة (صور، CSS، JS) جاهزة للربط بـ CloudFlare CDN
- **Gzip/Brotli**: ضغط الاستجابات من السيرفر
- **Database Indexing**: فهارس على slug, locale, status, category_id, published_at
- **Pagination**: لا تحميل 136 مقال دفعة واحدة - 12 مقال بالصفحة
- **الهدف**: Google PageSpeed Score > 90 (موبايل وديسكتوب)

### التصميم الرسمي والاحترافي:
- **تصميم مؤسسي نظيف**: لا ألوان صارخة، لا أنيميشن مبالغ فيه - موقع جمعية حقوق إنسان رسمي
- **Typography**: تدرج واضح بأحجام الخطوط (h1 > h2 > h3 > body > caption)
- **White Space**: مسافات كافية بين العناصر - لا ازدحام
- **Color Palette**: 2-3 ألوان رئيسية فقط + رمادي للنصوص
- **Grid System**: شبكة منتظمة ومتناسقة عبر كل الصفحات
- **Consistent Components**: كل الأزرار والكروت والنماذج بنفس الستايل
- **Micro-interactions**: hover effects بسيطة ومهنية فقط
- **لا يبين مبني بالذكاء**: كل component مكتوب يدوياً بعناية، بدون template جاهز واضح

### Code Style وجودة الكود (مهم جداً):

**PHP (Laravel):**
- PSR-12 coding standard
- نمط Repository Pattern للـ Models المعقدة
- Single Responsibility: كل class يسوي شي واحد فقط
- Meaningful names: `$publishedPosts` مو `$data`، `getVisibleCategories()` مو `getData()`
- Type hints على كل الـ parameters و return types
- PHP CS Fixer للفحص التلقائي

**Vue.js:**
- Composition API (setup script) بشكل موحد
- كل component بملف واحد (.vue) مع `<script setup>`, `<template>`, `<style scoped>`
- Props محددة النوع مع defaults
- Composables للمنطق المشترك (useLocale, useFontSize, useShare)
- ESLint + Prettier للتنسيق التلقائي

**تسمية الملفات:**
- Models: PascalCase مفرد (`Post.php`, `Category.php`)
- Controllers: PascalCase + Controller (`PostController.php`)
- Vue Pages: PascalCase (`PostShow.vue`, `CategoryIndex.vue`)
- Vue Components: PascalCase (`PostCard.vue`, `AppHeader.vue`)
- Migrations: snake_case مع تاريخ (`2024_01_01_create_posts_table.php`)
- CSS: kebab-case (`post-card`, `app-header`)

**Git:**
- Conventional Commits: `feat:`, `fix:`, `refactor:`, `style:`
- كل feature بـ branch منفصل
- لا commit كبير - commits صغيرة واضحة

### SEO:
- Inertia SSR (server-side rendering) عبر `@inertiajs/server`
- meta tags ديناميكية عبر `<Head>` component
- hreflang tags للربط بين الترجمات
- sitemap.xml تلقائي عبر `spatie/laravel-sitemap`

---

## 5. نقل البيانات (Migration)

أمر Artisan: `php artisan wp:migrate`

### الخطوات بالترتيب:
1. **المستخدمين** → نقل user 1 (admin) و user 5 (editor) مع أدوار جديدة
2. **الوسائط** → نسخ ~1,500 ملف من wp-content/uploads/ + إنشاء سجلات media
3. **التصنيفات** → نقل 7 تصنيفات مع ربط الترجمات (AR↔EN) والتفرعات
4. **المقالات** → نقل 136 مقال، ربط أزواج الترجمة من Polylang، تنظيف HTML
5. **الصفحات** → نقل 4 أزواج صفحات مترجمة
6. **القوائم** → نقل 4 قوائم مع العناصر المتداخلة
7. **تنظيف المحتوى** → حذف Gutenberg comments، تحويل URLs للصور، حذف shortcodes

---

## 6. الأمان والسكيورتي

### حماية لوحة التحكم:
- **Rate Limiting**: تحديد محاولات تسجيل الدخول (5 محاولات / 15 دقيقة) عبر Laravel throttle
- **2FA**: مصادقة ثنائية اختيارية للأدمن عبر Google Authenticator
- **Session Security**: تسجيل خروج تلقائي بعد 30 دقيقة عدم نشاط
- **IP Logging**: تسجيل IP لكل عملية دخول ناجحة/فاشلة
- **CSRF Protection**: حماية تلقائية من Laravel على كل الفورمات

### حماية الموقع:
- **XSS Protection**: تنظيف HTML المدخل عبر Mews/Purifier + Vue auto-escaping
- **SQL Injection**: Eloquent ORM + prepared statements (لا raw queries)
- **File Upload Security**: التحقق من نوع الملف الحقيقي (MIME)، حد أقصى للحجم، تغيير اسم الملف
- **CORS**: تقييد الطلبات للدومين الخاص فقط
- **Content Security Policy (CSP)**: هيدرات أمان على كل الاستجابات
- **HTTPS Only**: إجبار SSL على كل الصفحات

### حماية قاعدة البيانات:
- **Soft Deletes**: لا حذف نهائي - كل شي قابل للاسترجاع
- **Backup تلقائي**: نسخ احتياطي يومي عبر `spatie/laravel-backup`
- **كلمات مرور مشفرة**: bcrypt مع cost factor 12

### التحديثات:
- **Composer audit**: فحص الحزم لثغرات معروفة
- **npm audit**: فحص حزم JavaScript
- **Dependabot/Renovate**: تحديثات أمنية تلقائية عبر GitHub
- **Laravel Security Releases**: متابعة تحديثات Laravel الأمنية

### حماية الـ Endpoints (مهم جداً):

**IDOR Prevention (Insecure Direct Object Reference):**
- كل endpoint محمي بـ **Policy** → المستخدم يشوف بس البيانات المسموح له بيها
- استخدام `authorizeResource()` بكل controller
- لا نستخدم ID مباشر بالـ URL للعمليات الحساسة → نستخدم UUID
- `PostPolicy@update` → يتأكد إن المستخدم هو الكاتب أو admin
- `PostPolicy@delete` → admin فقط
- الزائر العادي يشوف فقط المنشور (`status = published`) ولا يقدر يوصل لـ draft أو archived

**Authorization Bypass Prevention:**
- كل route محمي بـ middleware مناسب:
  - `/admin/*` → `auth` + `verified` + `role:admin|editor`
  - الفورمات العامة (اتصل بنا) → `throttle` + `recaptcha`
  - عمليات الحذف → `role:admin` فقط
- **Gate checks** على مستوى الـ Controller AND مستوى الـ Model
- لا نعتمد على الفرونت إند للحماية → كل شي يتحقق بالباك إند

**Mass Assignment Protection:**
- كل Model فيه `$fillable` محدد بالحقول المسموحة فقط
- لا نستخدم `$guarded = []` أبداً
- Form Requests تمنع إرسال حقول إضافية (`validated()` فقط)

**Rate Limiting:**
- تسجيل دخول: 5 محاولات / 15 دقيقة
- نموذج اتصال: 3 رسائل / ساعة لكل IP
- بحث: 30 طلب / دقيقة
- عام: 60 طلب / دقيقة لكل IP

**SQL Injection Prevention:**
- Eloquent ORM فقط → لا raw queries
- لو اضطرينا لـ raw → `DB::select('... WHERE id = ?', [$id])` مع binding
- `whereIn()` بدل string concatenation

**Path Traversal / File Access:**
- رفع الملفات بمجلد `storage/app/public/media/` فقط
- التحقق من المسار → لا `../` مسموح
- الملفات تُخدم عبر Laravel (لا direct access)
- اسم الملف يتغير لـ UUID عند الرفع

**Session & Auth Security:**
- Session تنتهي بعد 30 دقيقة عدم نشاط
- تجديد session ID عند تسجيل الدخول (`session()->regenerate()`)
- HttpOnly + Secure + SameSite cookies
- حظر IP بعد 10 محاولات دخول فاشلة (24 ساعة)

### Middleware أمان مخصص:
- `SecurityHeaders` - إضافة هيدرات أمان (X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy)
- `TrustProxies` - التعامل مع reverse proxy / CloudFlare
- `ForceHttps` - إجبار HTTPS
- `PreventIdorAccess` - التحقق من الصلاحيات على كل طلب

---

## 7. مراحل التنفيذ

| المرحلة | المهام | المدة |
|---------|--------|-------|
| 1. الأساس | Laravel + Inertia + Vue 3 + migrations + models + Filament setup + أمان | أسبوع 1-2 |
| 2. الداشبورد | كل Filament Resources (مقالات، تصنيفات، وسائط، قوائم، إعدادات) | أسبوع 2-3 |
| 3. الفرونت | Vue 3 كل الصفحات والمكونات + RTL + SEO | أسبوع 3-5 |
| 4. النقل | سكربت نقل البيانات من WordPress + تحقق | أسبوع 5-6 |
| 5. الاختبار | تجربة شاملة + أمان + أداء | أسبوع 6 |
| 6. الإطلاق | نشر + DNS + SSL + backup | أسبوع 7 |

---

## 8. التحقق والاختبار

1. مقارنة عدد المقالات والتصنيفات بين WordPress والموقع الجديد
2. فتح كل صفحة بالعربي والإنكليزي والتأكد من المحتوى
3. تجربة رفع مقال جديد بلغتين مع صور ومرفقات PDF
4. تجربة إخفاء/إظهار تصنيف والتأكد يختفي من الموقع
5. تجربة نموذج الاتصال
6. فحص SEO (meta tags, sitemap, hreflang)
7. تجربة الموبايل (RTL + responsive)
8. Google PageSpeed test
9. **فحص أمني**: اختبار SQL injection, XSS, CSRF, file upload attacks
10. **اختبار Rate Limiting**: التأكد من حظر محاولات الدخول المتكررة
11. **اختبار Backup**: التأكد من عمل النسخ الاحتياطي واستعادته
