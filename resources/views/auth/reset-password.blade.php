<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu - AURELIA</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .row.g-0.flex-grow-1 {
            height: 100%;
        }

        /* Hero Carousel Styling */
        .split-left {
            height: 100%;
            position: relative;
            padding: 0;
            overflow: hidden;
            background-color: #f8f9fa;
        }

        .auth-carousel,
        .auth-carousel .carousel-inner,
        .auth-carousel .carousel-item {
            height: 100%;
            width: 100%;
        }

        .auth-carousel .carousel-item img {
            height: 100%;
            width: 100%;
            object-fit: contain;
            object-position: center;
            background-color: #ffffff; /* Sửa thành nền trắng để không bị lệch màu với ảnh gốc */
        }

        .split-right {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f7f6;
            padding: 30px 20px;
            overflow-y: auto;
        }

        .form-container {
            background-color: #ffffff;
            max-width: 440px;
            width: 100%;
            padding: 1.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .auth-logo {
            max-height: 55px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .auth-logo:hover {
            transform: scale(1.04);
        }

        .form-container h2 {
            font-weight: 700;
            font-size: 1.45rem;
            margin-bottom: 2px;
            color: #212529;
        }

        .form-container p.subtitle {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.82rem;
            color: #495057;
            margin-bottom: 0.2rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: #6c757d;
            padding: 0.4rem 0.7rem;
        }

        .form-control {
            border-left: none;
            padding: 0.4rem 0.7rem;
            font-size: 0.88rem;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(253, 129, 122, 0.25);
            border-radius: 0.375rem;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #FD817A;
        }

        .btn-primary {
            background-color: #FD817A;
            border-color: #FD817A;
            border-radius: 8px;
            padding: 9px 16px;
            font-weight: 600;
            font-size: 0.92rem;
            margin-top: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: #e86e67;
            border-color: #e86e67;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(253, 129, 122, 0.35);
        }

        .login-link {
            color: #FD817A;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
        }

        .login-link:hover {
            color: #e86e67;
            text-decoration: underline;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        /* Footer Styling */
        .auth-footer {
            background-color: #ffffff;
            border-top: 1px solid #eef0f3;
            flex-shrink: 0;
            padding: 11px 0;
            position: relative; 
            z-index: 10;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.02);
        }

        .footer-brand {
            font-family: 'Playfair Display', serif, system-ui;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 2px;
            color: #FD817A;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .footer-brand:hover {
            color: #e86e67;
            opacity: 0.9;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
        }

        .footer-link {
            color: #4a5568;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .footer-link:hover {
            color: #FD817A;
            text-decoration: none;
        }

        .copyright-text {
            font-size: 0.74rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            color: #8896a6;
            text-transform: uppercase;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <!-- Đã thêm class d-flex và flex-column -->
    <div class="main-content container-fluid p-0 d-flex flex-column">
        <!-- Đã thêm class flex-grow-1 để dãn full màn -->
        <div class="row g-0 flex-grow-1">
            <!-- Left Side (Hero Carousel) -->
            <div class="col-lg-6 d-none d-lg-block split-left">
                <div id="authHeroCarousel" class="carousel slide carousel-fade auth-carousel" data-bs-ride="carousel" data-bs-pause="false">
                    <div class="carousel-inner">
                        <div class="carousel-item active" data-bs-interval="8000">
                            <img src="{{ asset('images/anhloginwebbanvay.jpg') }}" alt="Aurelia Collection 1">
                        </div>
                        <div class="carousel-item" data-bs-interval="8000">
                            <img src="{{ asset('images/chan-vay-den-cong-so-dang-chu-a-xoe-cv05-34.jpg') }}" alt="Aurelia Collection 2">
                        </div>
                        <div class="carousel-item" data-bs-interval="8000">
                            <img src="{{ asset('images/anhloginnn.jpg') }}" alt="Aurelia Collection 3">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side (Form) -->
            <div class="col-lg-6 split-right">
                <div class="form-container">
                    <!-- Logo Aurelia -->
                    <div class="text-center mb-2">
                        <a href="/">
                            <img src="{{ asset('images/nenlogoaureliawwhite.png') }}" alt="Aurelia Logo" class="auth-logo img-fluid">
                        </a>
                    </div>

                    <h2 class="text-center">Đặt Lại Mật Khẩu</h2>
                    <p class="subtitle text-center">Tạo mật khẩu mới cho tài khoản của bạn.</p>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email Address -->
                        <div class="mb-2">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $request->email) }}" placeholder="Nhập địa chỉ email" required autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-2">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control border-end-0 @error('password') is-invalid @enderror" id="password" name="password" placeholder="Nhập mật khẩu mới" required autocomplete="new-password">
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-2">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                <input type="password" class="form-control border-end-0 @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" required autocomplete="new-password">
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Cập Nhật Mật Khẩu
                        </button>
                        
                        <div class="text-center">
                            <a class="login-link text-sm" href="{{ route('login') }}" style="font-size: 0.85rem;">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại Đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="auth-footer">
        <div class="container-fluid px-4 px-md-5">
            <div class="row align-items-center gy-2 justify-content-between">
                <!-- Left: Brand Name -->
                <div class="col-12 col-md-auto text-center text-md-start">
                    <a href="/" class="footer-brand d-inline-flex align-items-center gap-2 text-decoration-none">
                        <span>AURELIA</span>
                        <span class="d-none d-xl-inline text-muted" style="font-size: 0.72rem; font-family: 'Inter', sans-serif; font-weight: 400; letter-spacing: 0.5px; border-left: 1px solid #e2e8f0; padding-left: 8px;">BOUTIQUE</span>
                    </a>
                </div>

                <!-- Center: Navigation Links -->
                <div class="col-12 col-md-auto text-center">
                    <div class="footer-links">
                        <a href="#" class="footer-link">Bền Vững</a>
                        <a href="#" class="footer-link">Giao Hàng</a>
                        <a href="#" class="footer-link">Đổi Trả</a>
                        <a href="#" class="footer-link">Liên Hệ</a>
                        <a href="#" class="footer-link">Bảo Mật</a>
                    </div>
                </div>

                <!-- Right: Copyright -->
                <div class="col-12 col-md-auto text-center text-md-end">
                    <span class="copyright-text text-nowrap">© 2024 AURELIA BOUTIQUE. ĐÃ ĐĂNG KÝ BẢN QUYỀN.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
