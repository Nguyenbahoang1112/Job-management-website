<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect($this->redirectTo() . '?verified=1');
    }

    protected function redirectTo()
    {
        // URL frontend muốn chuyển đến, ví dụ trang login FE
        return 'http://192.168.0.183:3000/login';
    }
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Kiểm tra chữ ký link có hợp lệ không
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect($this->redirectTo());
        }

        // Nếu đã xác minh rồi thì cứ redirect thôi
        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectTo() . '?verified=1');
        }

        // Đánh dấu email đã xác minh
        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect($this->redirectTo() . '?verified=1');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email đã được xác minh.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email xác thực đã được gửi.']);
    }
}
