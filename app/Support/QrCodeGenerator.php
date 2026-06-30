<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeGenerator
{
    /**
     * يولّد رمز QR بصيغة SVG جاهز للتضمين داخل HTML (مناسب للطباعة).
     * لا يستخدم أي خدمة خارجية ولا يحتاج امتداد imagick.
     */
    public static function svg(string $data, int $size = 180): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($data);

        // إزالة مقدمة XML للتضمين النظيف داخل صفحة HTML
        if (($pos = strpos($svg, '<svg')) !== false) {
            $svg = substr($svg, $pos);
        }

        return $svg;
    }
}
