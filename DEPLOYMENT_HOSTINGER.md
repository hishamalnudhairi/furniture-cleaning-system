# دليل رفع النظام على Hostinger

دليل عملي بالعربية لرفع **نظام مغسلة الكنب والزوالي والسجاد** (Laravel 12 + MySQL) على استضافة Hostinger المشتركة.

> ⚠️ هذا الدليل توثيقي فقط. **لم يُرفع أي شيء فعليًا**، ولا توجد بيانات حقيقية في المشروع. نفّذ الخطوات بنفسك على Hostinger.

## 🗺️ نظرة سريعة على ترتيب الخطوات (للمبتدئ)
1. جهّز محليًا: `npm run build` + `php artisan test` (القسم 1).
2. أنشئ قاعدة MySQL في hPanel واحفظ بياناتها (القسم 2).
3. ارفع ملفات المشروع (Git أو FTP) (القسم 3).
4. أعدّ ملف `.env` على السيرفر + `key:generate` (القسم 4).
5. اضبط الجذر العام على مجلد `public` (القسم 5).
6. شغّل أوامر الإنتاج: `composer install` + `migrate` + `storage:link` + الكاش (القسم 6).
7. (أول مرة فقط) `db:seed --force` ثم **غيّر كلمة مرور المدير** (الأقسام 8 و9).
8. راجع **قائمة الفحص** واختبر الصفحات (القسم 10).

> الأقسام التالية تشرح كل خطوة بالتفصيل بالترتيب نفسه.

---

## 0) المتطلبات قبل البدء
- حساب Hostinger مع خطة تدعم **PHP 8.2+** و**MySQL** و**Composer** و**SSH** (يفضّل وجود SSH/Terminal في hPanel).
- دومين مربوط بالاستضافة + شهادة **SSL/HTTPS** مفعّلة.
- نسخة المشروع جاهزة محليًا وكل الاختبارات ناجحة (`php artisan test`).

---

## 1) تجهيز المشروع محليًا قبل الرفع
1. بناء الأصول الأمامية (مهم — حتى لا تحتاج npm على السيرفر):
   ```
   npm install
   npm run build
   ```
   تأكد أن مجلد `public/build/` موجود ويحتوي `manifest.json` والملفات داخل `public/build/assets/`.

   > ⚠️ **مهم — أصول الواجهة و Git:** مجلد `public/build` مُدرج في `.gitignore` افتراضيًا، أي **لن يُرفع عبر Git**. لذلك:
   > - إن رفعت عبر **FTP/File Manager** (الطريقة 3-ب): ارفع مجلد `public/build` يدويًا ضمن الملفات (سيعمل مباشرة).
   > - إن رفعت عبر **Git** (الطريقة 3-أ): إمّا شغّل `npm run build` على السيرفر (إن توفّر Node/npm)، أو ارفع `public/build` يدويًا عبر FTP بعد الـ clone، أو احذف سطر `/public/build` من `.gitignore` في فرعك الخاص بالنشر.
   > - النظام لا يحتاج `npm` وقت التشغيل طالما `public/build` موجود على السيرفر.
2. تأكد أن الاختبارات تعمل:
   ```
   php artisan test
   ```
3. **لا ترفع** هذه المجلدات/الملفات (سنثبّت الحزم على السيرفر أو نرفعها حسب الطريقة):
   - `node_modules/` (غير مطلوب على السيرفر إطلاقًا).
   - `.env` (سرّي — لا يُرفع إلى Git ولا يُشارك).
   - `.git/` (اختياري).

---

## 2) إنشاء قاعدة بيانات MySQL على Hostinger
من **hPanel → Databases → MySQL Databases**:
1. أنشئ قاعدة بيانات جديدة (مثال: `u123456_cleaning`).
2. أنشئ مستخدمًا وكلمة مرور قوية، وامنحه كل الصلاحيات على القاعدة.
3. احفظ هذه القيم (ستحتاجها في `.env`):
   - **DB_DATABASE** = اسم القاعدة كاملًا.
   - **DB_USERNAME** = اسم المستخدم كاملًا.
   - **DB_PASSWORD** = كلمة المرور.
   - **DB_HOST** = غالبًا `localhost` على Hostinger (وليس 127.0.0.1). إن لم تعمل، جرّب القيمة التي يعرضها hPanel.

---

## 3) رفع ملفات المشروع
اختر إحدى الطريقتين:

**أ) عبر Git (مستحسن إن توفّر SSH):**
```
cd ~/domains/your-domain.com
git clone <repo-url> app
cd app
composer install --no-dev --optimize-autoloader
```

**ب) عبر File Manager / FTP:**
- اضغط المشروع (بدون `node_modules` و`.git`) وارفعه إلى مجلد خارج الجذر العام مثل `~/domains/your-domain.com/app`.
- ثم نفّذ `composer install --no-dev --optimize-autoloader` من Terminal، أو ارفع مجلد `vendor/` كاملًا إذا لم يتوفر Composer على السيرفر.

> لا ترفع المشروع كاملًا داخل `public_html` مباشرة لأسباب أمنية (انظر القسم 5).

---

## 4) إعداد ملف `.env` على السيرفر
1. انسخ القالب:
   ```
   cp .env.production.example .env
   ```
2. عدّل القيم:
   - `APP_NAME`, `APP_URL=https://your-domain.com`
   - `APP_ENV=production`, `APP_DEBUG=false`
   - بيانات `DB_*` من الخطوة 2.
   - `APP_TIMEZONE=Asia/Muscat`, `APP_LOCALE=ar`, `APP_FALLBACK_LOCALE=en`
   - `FILESYSTEM_DISK=public`
3. توليد مفتاح التطبيق:
   ```
   php artisan key:generate
   ```

---

## 5) ضبط مجلد public (Document Root)
النظام مبني بحيث يكون الجذر العام هو مجلد `public/` فقط.

**الخيار الأفضل (إن سمحت الاستضافة):**
- وجّه **Document Root** للدومين إلى `~/domains/your-domain.com/app/public`.

**الخيار البديل للاستضافة المشتركة (عندما يكون الجذر `public_html` ثابتًا):**
1. ارفع كود المشروع إلى مجلد خاص خارج `public_html` (مثل `~/app`).
2. انقل **محتويات** `public/` فقط إلى `public_html/`.
3. عدّل `public_html/index.php` ليشير إلى مسار المشروع:
   ```php
   require __DIR__.'/../app/vendor/autoload.php';
   $app = require_once __DIR__.'/../app/bootstrap/app.php';
   ```
   (عدّل `../app/` إلى المسار الفعلي لمجلد المشروع.)
4. تأكد أن `public_html/build/` موجود (أي محتويات `public/build`).

> ⚠️ نفّذ النقل بحذر ولا تحذف الأصل قبل التأكد. **هذا الدليل لا ينفّذ النقل**، فقط يشرحه.

---

## 6) أوامر ما بعد الرفع (الإنتاج)
نفّذها من داخل مجلد المشروع على السيرفر بالترتيب:
```
composer install --no-dev --optimize-autoloader
php artisan key:generate          # مرة واحدة فقط إن لم يكن APP_KEY مضبوطًا
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**عند الحاجة لتنظيف الكاش بعد أي تعديل على `.env` أو الإعدادات:**
```
php artisan optimize:clear
```
> مهم: بعد أي تغيير في `.env` على الإنتاج، شغّل `php artisan config:clear` ثم `config:cache` من جديد.

---

## 7) ربط storage (الشعارات/البوسترات/صور الطلبات)
رفع الملفات يعتمد على قرص `public` المخزّن في `storage/app/public`، ويُعرض عبر رابط رمزي:
```
php artisan storage:link
```
هذا ينشئ `public/storage` → `storage/app/public`.

**إن لم يسمح Hostinger بإنشاء الرابط الرمزي (symlink):**
- أنشئ الرابط يدويًا من Terminal:
  ```
  ln -s ~/domains/your-domain.com/app/storage/app/public ~/domains/your-domain.com/app/public/storage
  ```
- أو إن كنت تستخدم `public_html`:
  ```
  ln -s ~/app/storage/app/public ~/public_html/storage
  ```
- إن تعذّر symlink تمامًا، أنشئ مجلد `public/storage` وانسخ إليه محتوى `storage/app/public` يدويًا (حل أخير، يتطلب تكرار النسخ عند كل رفع صور).

تأكد أن صلاحيات الكتابة متوفرة على:
```
chmod -R 775 storage bootstrap/cache
```

---

## 8) قاعدة البيانات: الترحيل والبيانات الأولية
- إنشاء الجداول:
  ```
  php artisan migrate --force
  ```
- **عند أول تثبيت فقط** (لتعبئة الأدوار/الخدمات الافتراضية/الإعدادات):
  ```
  php artisan db:seed --force
  ```
  > ⚠️ **لا تشغّل `db:seed` على نظام يحتوي بيانات حقيقية** إلا إذا كنت متأكدًا أن الـ seeders آمنة (تستخدم `updateOrCreate` ولا تحذف). seeders هذا المشروع آمنة لكنها تنشئ حسابًا تجريبيًا — انظر القسم 9.

---

## 9) ⚠️ بيانات الدخول الافتراضية (مهم جدًا)
الـ seeders تنشئ حسابين تجريبيين:
- مدير: `admin@example.com` / `password`
- موظف: `worker@example.com` / `password`

**إجراءات إلزامية بعد أول دخول:**
1. سجّل الدخول بحساب المدير فورًا.
2. أنشئ مديرًا حقيقيًا ببريد وكلمة مرور قويين (أو غيّر بيانات الحساب التجريبي).
3. غيّر كلمة المرور التجريبية أو عطّل/احذف الحساب التجريبي.
4. لا تترك كلمة المرور `password` على نظام مباشر إطلاقًا.

---

## 10) ✅ قائمة فحص الإنتاج (Checklist)
بعد الرفع تأكد من:
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` صحيح ويطابق الدومين
- [ ] HTTPS يعمل (شهادة SSL مفعّلة)
- [ ] قاعدة البيانات متصلة (لا أخطاء اتصال)
- [ ] `php artisan storage:link` يعمل وتظهر الصور
- [ ] صفحة `/request-service` تعمل بالعربية والإنجليزية
- [ ] `login` يعمل وتسجيل الخروج يعمل
- [ ] `dashboard` (`/dashboard`) يعرض المؤشرات الحقيقية
- [ ] صفحة الطلبات الرسمية `/admin/orders` تعمل وتعرض الطلبات
- [ ] الفاتورة `…/invoice` والإيصال `…/receipt` يعملان
- [ ] **QR Code** يظهر في الفاتورة عند وجود موقع
- [ ] رفع الشعار/البوستر/الصور يعمل ويُعرض
- [ ] تبديل اللغة (عربي RTL / إنجليزي LTR) يعمل
- [ ] الطباعة (`window.print`) تعمل في A4 والحراري
- [ ] الإعدادات `/admin/settings` للمدير فقط، والموظف يُمنع (403)
- [ ] التقارير `/admin/reports` للمدير فقط
- [ ] لا توجد أخطاء في `storage/logs/laravel.log`

---

## 11) حماية الملفات والأمان
- **لا ترفع `.env` إلى Git** (موجود في `.gitignore` افتراضيًا) ولا تشاركه.
- لا تشارك كلمات مرور قاعدة البيانات أو لوحة التحكم.
- تأكد أن جذر الموقع هو `public/` فقط؛ بقية المجلدات (`app`, `config`, `storage`, `.env`) يجب ألّا تكون قابلة للوصول من المتصفح.
- **لا تترك `APP_DEBUG=true` في الإنتاج** (يكشف معلومات حساسة).
- راقب `storage/logs/laravel.log` دوريًا.

---

## 12) النسخ الاحتياطي
- **قاعدة البيانات**: نسخة احتياطية دورية عبر hPanel → Databases → Export، أو:
  ```
  mysqldump -u DB_USERNAME -p DB_DATABASE > backup.sql
  ```
- **ملفات الرفع**: انسخ مجلد `storage/app/public` احتياطيًا بشكل دوري.
- **ملف `.env`**: احفظ نسخة منه في مكان آمن **خارج** `public_html`.

---

## 13) فحص Laravel قبل وبعد الرفع
شغّل محليًا (وكرّرها على السيرفر إن أمكن):
```
php artisan test
npm run build
php artisan route:list
```
تأكد أن الاختبارات تنجح، والبناء ينجح، وكل المسارات مسجّلة.

---

## 14) استكشاف الأخطاء الشائعة
- **500 بعد الرفع**: غالبًا `APP_KEY` غير مضبوط → `php artisan key:generate`؛ أو صلاحيات `storage`/`bootstrap/cache` → `chmod -R 775`.
- **الصور لا تظهر**: `storage:link` لم يُنفّذ أو الرابط الرمزي غير مدعوم → انظر القسم 7.
- **خطأ اتصال قاعدة البيانات**: راجع `DB_HOST` (جرّب `localhost`) وبيانات الاعتماد.
- **تغييرات `.env` لا تظهر**: نفّذ `php artisan optimize:clear` ثم أعد `config:cache`.
- **صفحة بيضاء/أصول غير محمّلة**: تأكد من رفع `public/build/` وأن `APP_URL` صحيح.

---

**ملاحظة أخيرة:** النظام لا يعتمد على npm في وقت التشغيل (الأصول مبنية مسبقًا في `public/build`)، ولا يحتاج Redis (يستخدم `database` للجلسات/الكاش/الطوابير)، مما يجعله مناسبًا للاستضافة المشتركة على Hostinger.
