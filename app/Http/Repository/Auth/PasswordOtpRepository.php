<?php

namespace App\Http\Repository\Auth;

use App\Models\PasswordOtp;
use Carbon\Carbon;

class PasswordOtpRepository
{
    public function create(string $email, string $otp)
    {
        return PasswordOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function verify(string $email, string $otp): bool
    {
        $record = PasswordOtp::where('email', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->first();

        return $record !== null;
    }

    public function deleteAllForEmail(string $email)
    {
        PasswordOtp::where('email', $email)->delete();
    }
}
