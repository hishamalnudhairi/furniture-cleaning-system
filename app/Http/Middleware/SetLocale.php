<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * اللغات المدعومة في النظام.
     *
     * @var array<int, string>
     */
    protected array $supported = ['ar', 'en'];

    /**
     * يضبط لغة التطبيق بناءً على اللغة المحفوظة في الجلسة،
     * وإلا يستخدم اللغة الافتراضية من الإعدادات.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (! in_array($locale, $this->supported, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
