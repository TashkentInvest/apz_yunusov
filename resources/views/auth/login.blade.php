
    <div class="login-wrapper">
        <!-- Background Pattern -->
        <div class="background-pattern"></div>

        <!-- Main Login Container -->
        <div class="login-container">
            <!-- Login Card -->
            <div class="login-card">
                <!-- Header Section -->
                <div class="login-header">
                    <!-- Logo Section -->
                    <div class="logo-section">
                        <img src="https://toshkentinvest.uz/assets/frontend/tild6238-3031-4265-a564-343037346231/tic_logo_blue.png"
                            alt="Toshkent Invest Logo" class="company-logo">
                    </div>

                    <!-- Welcome Message -->
                    <div class="welcome-message">
                        <p>Shaxsiy kabinetga kirish uchun ma'lumotlaringizni kiriting</p>
                    </div>
                </div>

                <!-- Login Form -->
                <div class="login-body">
                    <form method="POST" action="{{ route('login') }}" class="login-form">
                        @csrf

                        <!-- Email Field -->
                        <div class="form-group">
                            <div class="form-floating">
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" required autocomplete="email" autofocus
                                    placeholder="Elektron pochta">
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Elektron pochta
                                </label>
                            </div>
                            @error('email')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="form-group">
                            <div class="form-floating password-field">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password" required
                                    autocomplete="current-password" placeholder="Parol">
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Parol
                                </label>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="form-options">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-check" name="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember-check">
                                    Eslab qolish
                                </label>
                            </div>


                        </div>

                        <!-- Submit Button -->
                        <div class="form-submit">
                            <button type="submit" class="btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Tizimga kirish
                            </button>
                        </div>

                        <!-- Register Link -->
                        @guest
                            @if (Route::has('register'))
                                <div class="register-link">
                                    <p>Hisobingiz yo'qmi?
                                        <a href="{{ route('register') }}">Ro'yxatdan o'tish</a>
                                    </p>
                                </div>
                            @endif
                        @endguest
                    </form>
                </div>

                <!-- Footer Section -->
                <div class="login-footer">
                    <div class="system-info">
                        <div class="info-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Xavfsiz ulanish</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <span id="current-time"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Developer Contact Info -->
            <div class="developer-info">
                <div class="developer-card">
                    <div class="developer-header">
                        <i class="fas fa-code"></i>
                        <h4>Dasturchi ma'lumotlari</h4>
                    </div>
                    <div class="developer-details">
                        <div class="contact-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <strong>Senior Full Stack Developer</strong>
                                <span>Web Development Specialist</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>a.abdusattorov@toshkentinvest.uz</strong>
                                <span>Texnik yordam</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>+998 33 308-80-99</strong>
                                <span>Qo'llab-quvvatlash</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fab fa-telegram"></i>
                            <div>
                                <strong>@az_etc</strong>
                                <span>Tezkor aloqa</span>
                            </div>
                        </div>
                    </div>
                    <div class="developer-badge">
                        <i class="fas fa-certificate"></i>
                        <span>Sertifikatlangan dasturchi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Button -->
        <div class="support-button">
            <button type="button" class="btn-support" onclick="toggleDeveloperInfo()">
                <i class="fas fa-question-circle"></i>
                <span>Yordam</span>
            </button>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');

            if (password.getAttribute('type') === 'password') {
                password.setAttribute('type', 'text');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.setAttribute('type', 'password');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('uz-UZ', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Toggle developer info
        function toggleDeveloperInfo() {
            const developerInfo = document.querySelector('.developer-info');
            const supportBtn = document.querySelector('.btn-support');

            if (developerInfo.classList.contains('show')) {
                developerInfo.classList.remove('show');
                supportBtn.innerHTML = '<i class="fas fa-question-circle"></i><span>Yordam</span>';
            } else {
                developerInfo.classList.add('show');
                supportBtn.innerHTML = '<i class="fas fa-times"></i><span>Yopish</span>';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateTime();
            setInterval(updateTime, 1000);
        });

        // Form validation enhancement
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            if (!email.value.trim()) {
                e.preventDefault();
                email.focus();
                return;
            }

            if (!password.value.trim()) {
                e.preventDefault();
                password.focus();
                return;
            }

            // Show loading state
            const submitBtn = document.querySelector('.btn-login');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Tizimga kirilmoqda...';
            submitBtn.disabled = true;
        });
    </script>

    <style>
        :root {
            --primary-blue: #003366;
            --secondary-blue: #004080;
            --accent-gold: #FFD700;
            --light-blue: #E6F3FF;
            --success-green: #28a745;
            --warning-orange: #fd7e14;
            --error-red: #dc3545;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #dee2e6;
            --dark-gray: #495057;
            --shadow: rgba(0, 51, 102, 0.15);
            --border-radius: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .pt-sm-5 {
            padding-top: 0 !important;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow: hidden !important;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--white) 100%);
        }

        .login-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 25% 25%, rgba(0, 51, 102, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 215, 0, 0.1) 0%, transparent 50%);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .login-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 16px 64px var(--shadow);
            overflow: hidden;
            width: 450px;
            max-width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            padding: 40px 30px;
            text-align: center;
            color: var(--white);
        }

        .logo-section {
            margin-bottom: 30px;
        }

        .company-logo {
            max-width: 200px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        .company-info {
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .company-type {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .test-badge {
            background: var(--accent-gold);
            color: var(--primary-blue);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .welcome-message h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .welcome-message p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-floating {
            position: relative;
        }

        .form-control {
            height: 56px;
            border: 2px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            padding-left: 16px;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }

        .form-floating>label {
            padding-left: 16px;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--dark-gray);
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: var(--transition);
            z-index: 10;
        }

        .password-toggle:hover {
            background: var(--light-gray);
        }

        .error-message {
            color: var(--error-red);
            font-size: 14px;
            margin-top: 8px;
            padding: 8px 16px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 6px;
            border-left: 4px solid var(--error-red);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
        }

        .form-check-label {
            font-size: 14px;
            color: var(--dark-gray);
        }

        .forgot-password {
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border: none;
            border-radius: var(--border-radius);
            color: var(--white);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 102, 0.3);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--medium-gray);
        }

        .register-link p {
            margin: 0;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .register-link a {
            color: var(--primary-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .login-footer {
            background: var(--light-gray);
            padding: 20px 30px;
            border-top: 1px solid var(--medium-gray);
        }

        .system-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .info-item i {
            color: var(--primary-blue);
        }

        /* Developer Info */
        .developer-info {
            position: fixed;
            right: -400px;
            top: 50%;
            transform: translateY(-50%);
            width: 380px;
            transition: var(--transition);
            z-index: 1000;
        }

        .developer-info.show {
            right: 20px;
        }

        .developer-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 12px 48px var(--shadow);
            overflow: hidden;
        }

        .developer-header {
            background: linear-gradient(135deg, var(--success-green), #20c997);
            color: var(--white);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .developer-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .developer-details {
            padding: 24px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .contact-item:last-child {
            margin-bottom: 0;
        }

        .contact-item i {
            width: 20px;
            color: var(--primary-blue);
            margin-top: 4px;
        }

        .contact-item div strong {
            display: block;
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 2px;
        }

        .contact-item div span {
            color: var(--dark-gray);
            font-size: 13px;
        }

        .developer-badge {
            background: var(--light-blue);
            padding: 16px 24px;
            border-top: 1px solid var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-blue);
            font-size: 14px;
            font-weight: 600;
        }

        .support-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
        }

        .btn-support {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 50px;
            padding: 16px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 24px var(--shadow);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-support:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 51, 102, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                align-items: center;
            }

            .login-card {
                width: 100%;
                max-width: 400px;
            }

            .developer-info {
                width: calc(100% - 40px);
                right: -100%;
            }

            .developer-info.show {
                right: 20px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 10px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .company-name {
                font-size: 24px;
            }

            .support-button {
                bottom: 20px;
                right: 20px;
            }

            .btn-support span {
                display: none;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
            }
        }
    </style>
