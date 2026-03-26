@php
    $setting = \App\Models\Setting::first();
    $shortName = 'Puskomedia';
    if ($setting && $setting->company_name) {
        $parts = explode(' ', str_replace(['PT. ', 'CV. ', 'pt. ', 'cv. ', 'Pt. ', 'Cv. '], '', $setting->company_name));
        $shortName = $parts[0] ?? 'Puskomedia';
    }
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->company_name ?? 'Login - DMS App' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1200px;
            width: 90%;
            display: grid;
            grid-template-columns: 1fr 420px;
            min-height: 600px;
        }

        /* Left - Company Info */
        .company-info {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .company-title {
            font-size: 36px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .company-subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .services {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .service-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .service-text h4 {
            font-size: 16px;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .service-text p {
            font-size: 14px;
            color: #64748b;
        }

        /* Right - Login Form */
        .login-section {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            opacity: 0.95;
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: white;
            color: #1e40af;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: rgba(255, 255, 255, 0.6);
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
        }

        .divider span {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            padding: 0 20px;
        }

        .alt-links {
            text-align: center;
        }

        .alt-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            display: block;
            margin: 8px 0;
            transition: color 0.3s ease;
        }

        .alt-links a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .main-container {
                grid-template-columns: 1fr;
                width: 95%;
                max-width: 400px;
            }

            .company-info {
                padding: 40px 30px;
            }

            .login-section {
                padding: 40px 30px;
            }
        }

        @media (max-width: 480px) {
            .company-title {
                font-size: 28px;
            }

            .services {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Left - Company Info -->
        <div class="company-info">
            <a href="/" class="logo">
                @if($setting && $setting->company_logo)
                    <img src="{{ Storage::url($setting->company_logo) }}" alt="Logo" style="height: 40px; width: auto; max-width: 150px; object-fit: contain;">
                @else
                    <span>🌐</span>
                    {{ $shortName }}
                @endif
            </a>
            <h1 class="company-title">{{ $setting->company_name ?? 'PT. Puskomedia Indonesia Kreatif' }}</h1>
            <p class="company-subtitle">
                {!! nl2br(e($setting->company_subtitle ?? "Internet Service Provider & Pengembangan Aplikasi\nTeknologi untuk masa depan bisnis Indonesia")) !!}
            </p>

            <div class="services">
                <div class="service-item">
                    <div class="service-icon">🌐</div>
                    <div class="service-text">
                        <h4>ISP Enterprise</h4>
                        <p>Koneksi dedicated 99.9% uptime</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="service-icon">💻</div>
                    <div class="service-text">
                        <h4>Aplikasi Custom</h4>
                        <p>Web, mobile, enterprise solution</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="service-icon">🔧</div>
                    <div class="service-text">
                        <h4>Infrastruktur IT</h4>
                        <p>Server, network, cloud lokal</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right - Login Form -->
        <div class="login-section">
            <h2 class="login-title">Masuk ke Sistem</h2>
            <p class="login-subtitle">Akses dashboard {{ $shortName }}</p>

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                
                @if ($errors->any())
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 12px; border-radius: 12px; margin-bottom: 24px; font-size: 14px; text-align: center;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="user@mail.co.id" required autofocus>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Masuk</button>
            </form>

            <div class="divider">
                <span>atau</span>
            </div>

            <div class="alt-links">
                <a href="#">Lupa Password?</a>
                <a href="#">Daftar Akun Baru</a>
            </div>
        </div>
    </div>
</body>

</html>
