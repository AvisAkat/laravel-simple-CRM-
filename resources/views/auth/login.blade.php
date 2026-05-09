<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('pageTitle')</title>
    <link rel="stylesheet" href="{{ asset('src/css/style.css') }}">
    <!-- Using Font Awesome for icons -->
    {{-- <link rel="stylesheet" href="{{ asset('src/css/fontawesome.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <!-- Login Page Wrapper Container -->
    <div id="loginContainer" class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-handshake login-logo-icon"></i>
                <h2>SimpleCRM Portal</h2>
                <p>Enter credentials to access your dashboard account</p>
            </div>

            <form action="{{ route('auth.login') }}" method="POST" id="crmLoginForm" style="padding: 0; margin: 0;">
                @csrf
                <x-form-alert />
                <div class="form-group">
                    <label for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" name="loginEmail" placeholder="e.g. admin@simplecrm.com"
                        value="{{ old('loginEmail') }}">
                        @error('loginEmail')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                </div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="loginPassword">Account Password</label>
                    <input type="password" id="loginPassword" name="loginPassword" placeholder="••••••••">
                    @error('loginPassword')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; justify-content: center; padding: 14px;">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Account
                </button>
            </form>

            <div
                style="margin-top: 24px; text-align: center; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button class="theme-toggle" id="themeToggle" type="button" title="Toggle Theme"
                    style="margin: 0 auto; display: flex;">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>


    <!-- Toast Container for Notifications -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Load JavaScript -->
    <script src="{{ asset('src/js/main.js') }}"></script>
</body>

</html>