#!/usr/bin/env bash
#
# deploy-hostinger.sh
# سكربت تحديث مشروع Laravel على استضافة Hostinger.
# يُشغَّل من داخل مجلد المشروع:
#   ~/domains/teal-anteater-481438.hostingersite.com/app
#
# لا يحذف قاعدة البيانات، ولا يشغّل db:seed، ولا يعدّل .env، ولا يطبع أسرارًا.

set -e

# ألوان بسيطة للرسائل
GREEN="\033[0;32m"; RED="\033[0;31m"; YELLOW="\033[1;33m"; NC="\033[0m"

step()  { echo -e "${GREEN}==> $1${NC}"; }
warn()  { echo -e "${YELLOW}!  $1${NC}"; }
fail()  { echo -e "${RED}✖ $1${NC}"; exit 1; }

# ----------------------------------------------------------------------
# 0) التأكد من أننا داخل مجلد المشروع الصحيح
# ----------------------------------------------------------------------
step "التحقق من مجلد المشروع..."
if [ ! -f "artisan" ] || [ ! -f "composer.json" ]; then
    fail "هذا ليس مجلد مشروع Laravel. ادخل إلى مجلد التطبيق (app) ثم أعد التشغيل."
fi
if [ ! -d "../public_html" ]; then
    fail "لم يتم العثور على مجلد ../public_html بجانب مجلد المشروع."
fi
echo "   المجلد الحالي: $(pwd)"

# ----------------------------------------------------------------------
# 1) سحب آخر التحديثات من Git
# ----------------------------------------------------------------------
step "سحب آخر التحديثات من GitHub (git pull origin master)..."
git pull origin master

# ----------------------------------------------------------------------
# 2) تثبيت حزم Composer للإنتاج
# ----------------------------------------------------------------------
step "تثبيت حزم Composer (بدون حزم التطوير)..."
composer install --no-dev --optimize-autoloader

# ----------------------------------------------------------------------
# 3) التأكد من وجود أصول الواجهة المبنية مسبقًا
#    (Hostinger لا يحتوي npm — يجب بناؤها محليًا ورفعها)
# ----------------------------------------------------------------------
step "التحقق من وجود أصول الواجهة (public/build)..."
if [ ! -f "public/build/manifest.json" ]; then
    fail "الملف public/build/manifest.json غير موجود.\n   المطلوب: شغّل على جهازك المحلي (npm run build) ثم ارفع مجلد public/build إلى GitHub:\n     npm run build\n     git add -f public/build\n     git commit -m \"Build assets\"\n     git push\n   ثم أعد تشغيل هذا السكربت."
fi
echo "   ✔ أصول الواجهة موجودة."

# ----------------------------------------------------------------------
# 4) ترحيل قاعدة البيانات وتحديث الكاش
#    (migrate --force فقط — بلا db:seed وبلا حذف بيانات)
# ----------------------------------------------------------------------
step "ترحيل قاعدة البيانات (migrate --force)..."
php artisan migrate --force

step "تنظيف الكاش القديم..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

step "إعادة بناء كاش الإنتاج..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ----------------------------------------------------------------------
# 5) نسخ ملفات public إلى public_html
# ----------------------------------------------------------------------
step "نسخ ملفات public إلى ../public_html ..."
cp -r public/* ../public_html/
# نسخ .htaccess (ملف مخفي لا يشمله النمط *)
if [ -f "public/.htaccess" ]; then
    cp public/.htaccess ../public_html/
fi

# ----------------------------------------------------------------------
# 6) تعديل مسارات index.php في public_html لتشير إلى مجلد app
# ----------------------------------------------------------------------
step "ضبط مسارات ../public_html/index.php ..."
INDEX="../public_html/index.php"
if [ ! -f "$INDEX" ]; then
    fail "لم يتم العثور على $INDEX بعد النسخ."
fi
# استبدال أي مسار autoload/bootstrap ليشير إلى ../app
sed -i "s#require .*vendor/autoload.php';#require __DIR__.'/../app/vendor/autoload.php';#g" "$INDEX"
sed -i "s#\$app = require_once .*bootstrap/app.php';#\$app = require_once __DIR__.'/../app/bootstrap/app.php';#g" "$INDEX"
echo "   ✔ تم ضبط المسارات."

# ----------------------------------------------------------------------
# 7) إعادة إنشاء رابط storage يدويًا (بدون artisan storage:link)
# ----------------------------------------------------------------------
step "إعادة إنشاء رابط storage ..."
rm -rf ../public_html/storage
ln -s ../app/storage/app/public ../public_html/storage
echo "   ✔ تم ربط storage."

# ----------------------------------------------------------------------
# النهاية
# ----------------------------------------------------------------------
echo -e "${GREEN}"
echo "======================================================"
echo "  ✅ تم تحديث المشروع على Hostinger بنجاح."
echo "  افتح الموقع وتأكد أن كل شيء يعمل."
echo "======================================================"
echo -e "${NC}"
