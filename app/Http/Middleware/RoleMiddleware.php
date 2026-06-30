<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * يتحقق من صلاحية الدور.
     *
     * الاستخدام في المسارات: ->middleware('role:admin') أو ->middleware('role:worker,accountant')
     * - المدير (admin) يصل لكل شيء دائمًا.
     * - غير المسجّل يُحوّل لصفحة الدخول.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // المدير يملك صلاحية الوصول الكاملة
        if ($user->role === User::ROLE_ADMIN) {
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403);
    }
}
