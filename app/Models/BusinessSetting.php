<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;

    /**
     * جدول إعدادات أحادي الصف (single-row). يُستخدم الصف الأول دائمًا.
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'available_locales' => 'array',
            'logo_path' => 'string',
            'poster_path' => 'string',
            'tax_enabled' => 'boolean',
            'tax_percentage' => 'decimal:2',
            'invoice_next_number' => 'integer',
            'invoice_show_logo' => 'boolean',
            'invoice_show_qr' => 'boolean',
            'thermal_enabled' => 'boolean',
            'thermal_paper_width' => 'integer',
            'thermal_font_size' => 'integer',
            'driver_default_commission_value' => 'decimal:2',
            'inventory_enabled' => 'boolean',
            'inventory_low_stock_threshold' => 'decimal:2',
            'require_customer_map_location' => 'boolean',
            'max_image_count' => 'integer',
            'max_image_size_kb' => 'integer',
            'prices_include_tax' => 'boolean',
            // المرحلة 10
            'show_logo_on_public_page' => 'boolean',
            'show_banner_on_public_page' => 'boolean',
            'show_tax_on_invoice' => 'boolean',
            'print_auto_open' => 'boolean',
            'public_request_enabled' => 'boolean',
            'allow_customer_image_uploads' => 'boolean',
            'show_working_hours_on_public_page' => 'boolean',
            'show_shop_contact_on_public_page' => 'boolean',
            'default_delivery_fee' => 'decimal:2',
            'count_pickup_as_delivery' => 'boolean',
            'count_delivery_as_delivery' => 'boolean',
            'allow_driver_payments' => 'boolean',
            'low_stock_alerts' => 'boolean',
            'allow_manual_inventory_adjustment' => 'boolean',
            'english_enabled' => 'boolean',
            'decimal_places' => 'integer',
        ];
    }

    /**
     * اسم المحل حسب اللغة الحالية مع تدرّج احتياطي آمن.
     */
    public function shopName(): string
    {
        $locale = app()->getLocale();
        $name = $locale === 'ar' ? $this->shop_name_ar : $this->shop_name_en;

        return $name
            ?: ($this->shop_name_ar ?: $this->shop_name_en ?: $this->business_name ?: $this->project_name ?: 'Furniture Cleaning System');
    }

    /**
     * الوصف القصير حسب اللغة.
     */
    public function shortDescription(): ?string
    {
        $locale = app()->getLocale();

        return ($locale === 'ar' ? $this->short_description_ar : $this->short_description_en)
            ?: ($this->short_description_ar ?: $this->short_description_en);
    }

    /**
     * نص أسفل الفاتورة حسب اللغة.
     */
    public function invoiceFooter(): ?string
    {
        $locale = app()->getLocale();

        return ($locale === 'ar' ? $this->invoice_footer_ar : $this->invoice_footer_en)
            ?: $this->invoice_footer_text;
    }

    /**
     * رسالة نجاح طلب العميل حسب اللغة.
     */
    public function publicSuccessMessage(): ?string
    {
        $locale = app()->getLocale();

        return ($locale === 'ar' ? $this->public_success_message_ar : $this->public_success_message_en) ?: null;
    }

    /**
     * رقم ضريبة القيمة المضافة (مع تدرّج احتياطي للعمود القديم).
     */
    public function vatNumber(): ?string
    {
        return $this->vat_number ?: $this->tax_number;
    }

    /**
     * يُرجع صف الإعدادات الوحيد (ينشئه بالقيم الافتراضية إن لم يوجد).
     */
    public static function current(): self
    {
        $settings = static::firstOrCreate([]);

        // عند الإنشاء، أعد التحميل من قاعدة البيانات حتى تُحمّل القيم الافتراضية للأعمدة.
        if ($settings->wasRecentlyCreated) {
            $settings->refresh();
        }

        return $settings;
    }
}
