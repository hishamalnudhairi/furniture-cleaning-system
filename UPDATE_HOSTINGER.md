# تحديث النظام على Hostinger (خطوات يومية)

دليل مختصر لرفع أي تعديل جديد إلى الموقع المباشر على Hostinger بأمان.

- مجلد المشروع على السيرفر: `~/domains/teal-anteater-481438.hostingersite.com/app`
- مجلد الموقع العام: `~/domains/teal-anteater-481438.hostingersite.com/public_html`

> ⚙️ الفكرة: تبني الأصول محليًا وترفعها إلى GitHub، ثم على السيرفر تشغّل `deploy-hostinger.sh` الذي يسحب التحديث ويجهّز كل شيء.

---

## أولًا: على جهازك المحلي (Windows)
بعد أي تعديل على الكود أو الواجهة:

```
npm run build
git add -A
git add -f public/build
git commit -m "Update system"
git push
```

> **لماذا `git add -f public/build`؟** لأن مجلد `public/build` مُدرج في `.gitignore`، والعلم `-f` يجبر Git على إضافته حتى تصل الأصول المبنية إلى GitHub (Hostinger لا يملك npm لبنائها).

---

## ثانيًا: على Hostinger عبر SSH
```
ssh -p 65002 u709427795@145.79.28.153
cd ~/domains/teal-anteater-481438.hostingersite.com/app
bash deploy-hostinger.sh
```

السكربت ينفّذ تلقائيًا: `git pull` → `composer install` → التحقق من `public/build` → `migrate --force` → تنظيف وإعادة بناء الكاش → نسخ `public` إلى `public_html` → ضبط `index.php` → إعادة ربط `storage` → رسالة نجاح.

> السكربت **لا** يحذف قاعدة البيانات، **ولا** يشغّل `db:seed`، **ولا** يعدّل `.env`.

---

## المشاكل الشائعة وحلولها

### 1) `public/build/manifest.json غير موجود`
معناه أن أصول الواجهة لم تُرفع. الحل على جهازك المحلي:
```
npm run build
git add -f public/build
git commit -m "Build assets"
git push
```
ثم أعد تشغيل `bash deploy-hostinger.sh` على السيرفر.

### 2) GitHub يطلب username/password عند `git pull`
GitHub لم يعد يقبل كلمة المرور العادية. الحلول:
- **الأسهل**: استخدم رابط SSH للمستودع بدل HTTPS:
  ```
  git remote set-url origin git@github.com:USERNAME/furniture-cleaning-system.git
  ```
  (بعد إضافة مفتاح SSH الخاص بالسيرفر إلى حساب GitHub).
- **أو** استخدم **Personal Access Token** بدل كلمة المرور عند الطلب (لا تحفظه في أي ملف).
- **أو** خزّن الاعتماد مؤقتًا: `git config --global credential.helper store` (يخزّنه على السيرفر — استخدمه بحذر).

### 3) ظهور HTTP 500 بعد التحديث
- تحقق من السجلّ: `tail -n 50 storage/logs/laravel.log`
- امسح الكاش وأعد بناءه:
  ```
  php artisan optimize:clear
  php artisan config:cache
  ```
- تأكد أن `APP_KEY` مضبوط في `.env` (لا تعدّل بقية `.env`).
- تأكد من الصلاحيات: `chmod -R 775 storage bootstrap/cache`

### 4) `index.php` رجع لمسارات Laravel القديمة
يحدث لأن نسخ `public/index.php` يستبدل المسارات المعدّلة. **السكربت يصلح هذا تلقائيًا** عبر `sed` بعد النسخ. للتحقق اليدوي:
```
cat ../public_html/index.php | grep -E "autoload|bootstrap"
```
يجب أن تكون:
```php
require __DIR__.'/../app/vendor/autoload.php';
$app = require_once __DIR__.'/../app/bootstrap/app.php';
```

### 5) `storage:link` لا يعمل بسبب تعطيل exec
بعض خطط Hostinger تعطّل `exec`/`symlink` من داخل PHP، فيفشل `php artisan storage:link`.
- **الحل**: السكربت ينشئ الرابط يدويًا عبر أمر Linux مباشرة:
  ```
  rm -rf ../public_html/storage
  ln -s ../app/storage/app/public ../public_html/storage
  ```
- إن مُنع الرابط الرمزي تمامًا، أنشئ مجلدًا وانسخ المحتوى يدويًا (حل أخير):
  ```
  mkdir -p ../public_html/storage
  cp -r storage/app/public/* ../public_html/storage/
  ```

### 6) `bash: deploy-hostinger.sh: bad interpreter` أو أخطاء نهايات الأسطر
إذا ظهر خطأ متعلق بـ `\r`، حوّل نهايات الأسطر إلى LF:
```
sed -i 's/\r$//' deploy-hostinger.sh
bash deploy-hostinger.sh
```
> ملاحظة: ملف `.gitattributes` في المشروع يضبط `*.sh` على `eol=lf` تلقائيًا، فالغالب ألّا تحتاج هذا.

---

## ملاحظات أمان
- لا تضع أي Token أو كلمة مرور داخل ملفات المشروع.
- لا ترفع `.env` إلى GitHub (مُتجاهَل أصلًا).
- خذ نسخة احتياطية لقاعدة البيانات قبل التحديثات الكبيرة (راجع `DEPLOYMENT_HOSTINGER.md` قسم النسخ الاحتياطي).
