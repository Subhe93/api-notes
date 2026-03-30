# Page Notes API

Laravel API للإضافة Chrome Extension - Page Notes

## المتطلبات

- PHP 8.1+
- MySQL 5.7+
- Composer

## التثبيت على Shared Hosting

### 1. رفع الملفات

ارفع جميع ملفات المشروع إلى السيرفر (مثلاً في مجلد `api` أو `page-notes-api`)

### 2. تثبيت Dependencies

```bash
cd /path/to/page-notes-api
composer install --no-dev --optimize-autoloader
```

### 3. إعداد ملف البيئة

```bash
cp .env.example .env
php artisan key:generate
```

ثم عدّل ملف `.env`:

```env
APP_URL=https://your-domain.com
APP_DEBUG=false

DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

GOOGLE_CLIENT_ID=your-google-client-id
```

### 4. إنشاء جداول قاعدة البيانات

```bash
php artisan migrate
```

### 5. إعداد المجلد العام

إذا كان السيرفر يستخدم `public_html`:

**الخيار 1:** أنشئ symbolic link
```bash
ln -s /path/to/page-notes-api/public /home/user/public_html/api
```

**الخيار 2:** انقل محتويات `public` إلى `public_html/api` وعدّل `index.php`:
```php
require __DIR__.'/../page-notes-api/vendor/autoload.php';
(require_once __DIR__.'/../page-notes-api/bootstrap/app.php')
```

### 6. صلاحيات المجلدات

```bash
chmod -R 775 storage bootstrap/cache
```

## API Endpoints

### المصادقة

| Method | Endpoint | الوصف |
|--------|----------|-------|
| POST | `/api/auth/google` | تسجيل الدخول بـ Google |
| GET | `/api/auth/me` | معلومات المستخدم الحالي |
| POST | `/api/auth/logout` | تسجيل الخروج |

### الملاحظات

| Method | Endpoint | الوصف |
|--------|----------|-------|
| GET | `/api/notes` | جلب جميع الملاحظات |
| POST | `/api/notes` | إضافة/تحديث ملاحظة |
| POST | `/api/notes/sync` | مزامنة الملاحظات |
| DELETE | `/api/notes/{url}` | حذف ملاحظة |

### المشاركة

| Method | Endpoint | الوصف |
|--------|----------|-------|
| GET | `/api/shares` | المشاركات التي أنشأتها |
| GET | `/api/shares/received` | المشاركات المستلمة |
| GET | `/api/shares/notes` | الملاحظات المشاركة معي |
| POST | `/api/shares/domain` | مشاركة حسب الدومين |
| POST | `/api/shares/tag` | مشاركة حسب التاج |
| DELETE | `/api/shares/{id}` | إلغاء مشاركة |
| GET | `/api/users/search` | البحث عن مستخدمين |

## إعداد Google OAuth

1. اذهب إلى [Google Cloud Console](https://console.cloud.google.com/)
2. أنشئ مشروع جديد
3. فعّل Google+ API
4. أنشئ OAuth 2.0 Client ID
5. أضف Client ID في ملف `.env`

## تحديث الإضافة

عدّل `API_BASE_URL` في ملف `api.js` في الإضافة:

```javascript
const API_BASE_URL = 'https://your-domain.com/api';
```

## هيكل قاعدة البيانات

### جدول users
- `id` - معرف المستخدم
- `google_id` - معرف Google
- `name` - الاسم
- `email` - البريد الإلكتروني
- `avatar` - صورة الملف الشخصي

### جدول notes
- `id` - معرف الملاحظة
- `user_id` - معرف المستخدم
- `url` - رابط الصفحة
- `title` - عنوان الصفحة
- `tags` - التاجات (JSON)
- `notes_data` - بيانات الملاحظات (JSON)

### جدول shares
- `id` - معرف المشاركة
- `owner_id` - معرف المالك
- `shared_with_id` - معرف المستخدم المشارك معه
- `type` - نوع المشاركة (domain/tag)
- `value` - قيمة المشاركة
- `permissions` - الصلاحيات (read/write)
