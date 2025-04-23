<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\ResetPasswordWithOtpRequest;
use App\Http\Repository\Auth\PasswordOtpRepository;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\PasswordOtp;
class ForgotPasswordController extends Controller
{
    protected $otpRepo;

    public function __construct(PasswordOtpRepository $otpRepo)
    {
        $this->otpRepo = $otpRepo;
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $otp = rand(100000, 999999);
        $this->otpRepo->deleteAllForEmail($request->email);
        $this->otpRepo->create($request->email, $otp);

        Mail::raw("Mã OTP đặt lại mật khẩu của bạn là: $otp", function ($message) use ($request) {
            $message->to($request->email)->subject('Khôi phục mật khẩu');
        });

        return response()->json(['message' => 'OTP đã được gửi qua email.']);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required'],
            'password' => ['required', 'confirmed', 'min:6']
        ]);
    
        $otp = User::where('email', $request->email)
                  ->where('otp', $request->otp)
                  ->where('otp_expires_at', '>=', now())
                  ->first();
    
        if (!$otp) {
            return response()->json(['message' => 'OTP không đúng hoặc đã hết hạn.'], 422);
        }
    
        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        $this->otpRepo->deleteAllForEmail($user->email);
    
        return response()->json(['message' => 'Đặt lại mật khẩu thành công.']);
    }
    

    public function verifyOtp(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
        'otp' => ['required']
    ]);

    $otp = User::where('email', $request->email)
              ->where('otp', $request->otp)
              ->where('otp_expires_at', '>=', now()->addMinutes(10)) // Hạn OTP 10 phút
              ->first();

    if (!$otp) {
        return response()->json(['message' => 'OTP không đúng hoặc đã hết hạn.'], 422);
    }

    return response()->json(['message' => 'Xác thực OTP thành công.']);
}

}


