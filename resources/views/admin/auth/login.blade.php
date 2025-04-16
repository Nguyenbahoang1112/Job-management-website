<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('admin/css/login.css') }}">
</head>

<body>
    <div class="container">
        <div class="content">
            <div class="content__login">
                <p class="heading">LOGIN</p>
                <form action="" method="POST">
                    @csrf
                    <input type="text" placeholder="Input your email" required>
                    <input type="password" placeholder="Input your password" required />

                    <div class="options">
                        <label>
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit">Login</button>

                    {{-- <div class="social-login">
                        <p>Or login with</p>
                        <div class="social-buttons">
                            <button class="google">Google</button>
                            <button class="facebook">Facebook</button>
                        </div>
                    </div> --}}

                    <p class="register-link">
                        Don't have an account? <a href="{{ route('admin.register') }}">Register</a>
                    </p>
                </form>
            </div>
        </div>
        <div class="wrapper">
            <img class="wrapper__image" src="{{ asset('uploads/banners/loginBanner.jpg') }}" alt="">
        </div>
    </div>
</body>

</html>
