{{--
| حقن سمة المتجر (ألوان وخط) من الإعدادات إلى متغيّرات CSS وقت التشغيل.
| عرضٌ فقط: لا يغيّر أي منطق أو بيانات — يقرأ القيم المخزّنة ويشتق منها تدرّج لون العلامة.
| إن كانت القيم فارغة/افتراضية، لا يُطبع شيء ويبقى المظهر الافتراضي (teal) كما هو.
--}}
@php
    $themeSettings = \App\Models\BusinessSetting::current();

    // تطبيع قيمة لون سداسية إلى 6 خانات كبيرة بلا #، أو null إن كانت غير صالحة.
    $normHex = function ($hex) {
        $hex = ltrim(trim((string) $hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return (strlen($hex) === 6 && ctype_xdigit($hex)) ? strtoupper($hex) : null;
    };

    // مزج لونين: $weight نسبة اللون $with (0 = الأصل، 1 = $with).
    $mix = function ($hex, $with, $weight) {
        $a = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        $b = [hexdec(substr($with, 0, 2)), hexdec(substr($with, 2, 2)), hexdec(substr($with, 4, 2))];
        return sprintf(
            '#%02x%02x%02x',
            (int) round($a[0] * (1 - $weight) + $b[0] * $weight),
            (int) round($a[1] * (1 - $weight) + $b[1] * $weight),
            (int) round($a[2] * (1 - $weight) + $b[2] * $weight),
        );
    };

    $primary = $normHex($themeSettings->primary_color ?? null);
    $button  = $normHex($themeSettings->button_color ?? null);
    $bg       = $normHex($themeSettings->background_color ?? null);
    $font     = trim((string) ($themeSettings->default_font ?? ''));

    $vars = [];
    $WHITE = 'FFFFFF';
    $BLACK = '000000';

    // اشتقاق تدرّج كامل من لون العلامة (يتجاوز teal الافتراضي فقط عند اختلافه).
    if ($primary && $primary !== '0D9488') {
        $vars['--color-brand-50']  = $mix($primary, $WHITE, 0.90);
        $vars['--color-brand-100'] = $mix($primary, $WHITE, 0.80);
        $vars['--color-brand-200'] = $mix($primary, $WHITE, 0.64);
        $vars['--color-brand-300'] = $mix($primary, $WHITE, 0.46);
        $vars['--color-brand-400'] = $mix($primary, $WHITE, 0.24);
        $vars['--color-brand-500'] = $mix($primary, $WHITE, 0.10);
        $vars['--color-brand-600'] = '#'.strtolower($primary);
        $vars['--color-brand-700'] = $mix($primary, $BLACK, 0.16);
        $vars['--color-brand-800'] = $mix($primary, $BLACK, 0.30);
        $vars['--color-brand-900'] = $mix($primary, $BLACK, 0.44);
        $vars['--color-brand-950'] = $mix($primary, $BLACK, 0.60);
    }

    // لون زر أساسي مخصّص (يُبقي بقية التدرّج على لون العلامة).
    if ($button && $button !== ($primary ?: '0D9488')) {
        $vars['--color-brand-600'] = '#'.strtolower($button);
        $vars['--color-brand-700'] = $mix($button, $BLACK, 0.16);
    }

    // خلفية عامة للتطبيق.
    if ($bg && $bg !== 'F8FAFC') {
        $vars['--app-bg'] = '#'.strtolower($bg);
    }

    // خط افتراضي مخصّص يُقدَّم قبل Tajawal.
    if ($font !== '') {
        $vars['--font-sans'] = "'".str_replace(["'", '"', ';'], '', $font)."', 'Tajawal', ui-sans-serif, system-ui, sans-serif";
    }
@endphp
@if (!empty($vars))
<style>:root {
@foreach ($vars as $key => $value) {{ $key }}: {{ $value }};
@endforeach
}</style>
@endif
