<?php

namespace App\Http\Middleware;

use App\Helpers\RedirectResponse;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('sanctum')->check() && auth('sanctum')->user()->role == User::ROLE_ADMIN) {
            return $next($request);
        } else {
            return RedirectResponse::redirectWithMessage('admin.auth.login', RedirectResponse::ERROR, 'Bạn không có quyền truy cập vào trang này!');
        }
    }
}
