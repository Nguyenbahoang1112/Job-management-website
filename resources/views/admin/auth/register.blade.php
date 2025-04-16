<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('frontend/css/register.css') }}">
</head>

<body>
    <div class="container">
        <div class="content">
            <div class="content__register">
                <p class="heading">REGISTER</p>
                <form action="" method="POST">
                    @csrf
                    {{-- <input type="text" placeholder="Full Name" required> --}}
                    <input type="text" placeholder="Input email" required>
                    <input type="password" placeholder="Password" required>
                    <input type="password" placeholder="Confirm Password" required>

                    <button type="submit">Register</button>

                    {{-- <div class="social-login">
                        <p>Or register with</p>
                        <div class="social-buttons">
                            <button class="google">Google</button>
                            <button class="facebook">Facebook</button>
                        </div>
                    </div> --}}

                    <p class="login-link">
                        Already have an account? <a href="{{ route('admin.login') }}">Login</a>
                    </p>
                </form>
            </div>
        </div>
        <div class="wrapper">
            <img class="wrapper__image" src="{{ asset('uploads/banners/registerBanner.jpg') }}" alt="">
        </div>
    </div>
</body>

</html>
