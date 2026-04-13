<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DMS — Sistema de Gestión Documental</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        /* ── PANEL IZQUIERDO ── */
        .login-panel {
            width: 420px;
            min-width: 420px;
            height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 36px;
            z-index: 10;
            box-shadow: 4px 0 24px rgba(0,0,0,.10);
            overflow-y: auto;
        }

        /* Logo */
        .logo-area { text-align: center; margin-bottom: 28px; }
        .logo-icon {
            width: 60px; height: 60px;
            background: #185FA5;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .logo-icon svg { width: 30px; height: 30px; fill: white; }
        .logo-title { font-size: 20px; font-weight: 700; color: #0c1a2e; }
        .logo-sub   { font-size: 12px; color: #888; margin-top: 3px; }

        /* Tabs */
        .nav-tabs { border-bottom: 1.5px solid #e0e0e0; margin-bottom: 24px; }
        .nav-tabs .nav-link {
            font-size: 13px; font-weight: 500;
            color: #888; border: none;
            padding: 8px 16px;
            border-bottom: 2px solid transparent;
            margin-bottom: -1.5px;
            background: none;
            border-radius: 0;
            transition: color .2s, border-color .2s;
        }
        .nav-tabs .nav-link:hover { color: #185FA5; }
        .nav-tabs .nav-link.active {
            color: #185FA5;
            border-bottom-color: #185FA5;
            background: none;
        }

        /* Inputs */
        .form-label {
            font-size: 12px; font-weight: 500;
            color: #555; margin-bottom: 5px;
        }
        .form-control {
            height: 42px; font-size: 14px;
            border: 1px solid #ddd; border-radius: 8px;
            padding: 0 14px; outline: none;
            font-family: inherit;
            transition: border-color .2s;
        }
        .form-control:focus {
            border-color: #185FA5;
            box-shadow: 0 0 0 3px rgba(24,95,165,.12);
        }

        /* Botón primario */
        .btn-dms {
            width: 100%; height: 44px;
            background: #185FA5; color: white;
            border: none; border-radius: 8px;
            font-size: 14px; font-weight: 500;
            cursor: pointer; font-family: inherit;
            transition: background .2s;
            display: flex; align-items: center;
            justify-content: center; gap: 8px;
        }
        .btn-dms:hover { background: #1450890; }

        /* Botón Google */
        .btn-google {
            width: 100%; height: 44px;
            background: white; color: #333;
            border: 1.5px solid #ddd; border-radius: 8px;
            font-size: 14px; font-weight: 500;
            cursor: pointer; font-family: inherit;
            display: flex; align-items: center;
            justify-content: center; gap: 10px;
            text-decoration: none;
            transition: background .2s, border-color .2s, box-shadow .2s;
        }
        .btn-google:hover {
            background: #f9f9f9;
            border-color: #bbb;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            color: #333;
        }
        .btn-google svg { width: 20px; height: 20px; flex-shrink: 0; }

        /* Divider */
        .divider {
            display: flex; align-items: center;
            gap: 10px; margin: 18px 0;
            color: #bbb; font-size: 12px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: #e8e8e8;
        }

        /* Alertas */
        .alert-error {
            background: #FCEBEB; color: #A32D2D;
            border: 1px solid #f5c6c6;
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; margin-bottom: 16px;
        }
        .alert-success {
            background: #EAF3DE; color: #27500A;
            border: 1px solid #c3e6a0;
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; margin-bottom: 16px;
        }

        /* Info box Google */
        .google-info {
            background: #f0f7ff;
            border: 1px solid #c8e0f7;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #185FA5;
        }
        .google-info strong { display: block; margin-bottom: 4px; font-size: 14px; }
        .google-info span   { color: #555; font-size: 12px; }

        /* Remember + forgot */
        .form-check-label { font-size: 13px; color: #555; cursor: pointer; }
        .forgot-link {
            font-size: 12px; color: #185FA5;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Footer */
        .login-footer {
            text-align: center;
            font-size: 11px;
            color: #aaa;
            margin-top: 28px;
        }

        /* Badge institución */
        .inst-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0f7ff; border: 1px solid #c8e0f7;
            border-radius: 20px; padding: 4px 12px;
            font-size: 11px; color: #185FA5;
            font-weight: 500; margin-bottom: 20px;
        }
        .inst-badge::before {
            content: '🏛️';
        }

        /* ── PANEL DERECHO — Carousel ── */
        .carousel-panel {
            flex: 1;
            position: relative;
            overflow: hidden;
        }
        .carousel, .carousel-inner,
        .carousel-item { height: 100vh; }
        .carousel-item {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Overlay sobre el carousel */
        .carousel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(12,26,46,.55) 0%, rgba(24,95,165,.35) 100%);
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding-bottom: 48px;
        }
        .carousel-tagline {
            color: white;
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            text-shadow: 0 2px 8px rgba(0,0,0,.4);
            max-width: 480px;
            line-height: 1.4;
        }
        .carousel-tagline span {
            display: block;
            font-size: 14px;
            font-weight: 400;
            opacity: .85;
            margin-top: 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-panel   { width: 100%; min-width: unset; }
            .carousel-panel { display: none; }
        }
    </style>
</head>

<body>

    <!-- ══════════════════════════════════════
         PANEL IZQUIERDO — Login
    ══════════════════════════════════════ -->
    <div class="login-panel">

        <!-- Logo -->
        <div class="logo-area">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                </svg>
            </div>
            <div class="logo-title">DMS Universidad</div>
            <div class="logo-sub">Sistema de Gestión Documental</div>
        </div>

        <!-- Badge institución -->
        <div class="text-center">
            <span class="inst-badge">Universidad Nacional de Cañete</span>
        </div>

        <!-- Alertas globales -->
        @if ($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- ── TABS ── -->
        <ul class="nav nav-tabs" id="loginTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    id="email-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-email"
                    type="button"
                    role="tab"
                >
                    Correo y contraseña
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="google-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-google"
                    type="button"
                    role="tab"
                >
                    Cuenta institucional
                </button>
            </li>
        </ul>

        <div class="tab-content" id="loginTabsContent">

            <!-- ── TAB 1: Email + Password ── -->
            <div class="tab-pane fade show active" id="tab-email" role="tabpanel">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            placeholder="correo@universidad.edu.pe"
                            required
                            autofocus
                        />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                            />
                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>
                        {{-- Descomentar si implementas reset de contraseña
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                        --}}
                    </div>

                    <button type="submit" class="btn-dms">
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:white">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                        </svg>
                        Iniciar sesión
                    </button>

                    <div class="divider">o</div>

                    <!-- Google también disponible desde tab email -->
                    <a href="{{ route('auth.google') }}" class="btn-google">
                        <svg viewBox="0 0 48 48">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                        Continuar con Google
                    </a>
                </form>
            </div>

            <!-- ── TAB 2: Cuenta institucional (Google) ── -->
            <div class="tab-pane fade" id="tab-google" role="tabpanel">

                <div class="google-info">
                    <strong>🔐 Acceso con cuenta institucional</strong>
                    <span>Usa tu correo <b>@undc.edu.pe</b> para acceder al sistema de forma segura mediante Google.</span>
                </div>

                <a href="{{ route('auth.google') }}" class="btn-google mb-3">
                    <svg viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Iniciar sesión con Google
                </a>

                <p class="text-center" style="font-size:11px; color:#aaa; line-height:1.5;">
                    Al iniciar sesión aceptas los
                    <a href="https://web.undc.edu.pe/terminos-y-condiciones/" style="color:#185FA5">términos y condiciones</a>
                    y la
                    <a href="https://web.undc.edu.pe/pdatospersonales/" style="color:#185FA5">política de privacidad</a>
                    de la universidad.
                </p>
            </div>

        </div>

        <!-- Footer -->
        <div class="login-footer">
            © {{ date('Y') }} Universidad Nacional de Cañete — DMS v1.0
        </div>
    </div>

    <!-- ══════════════════════════════════════
         PANEL DERECHO — Carousel
    ══════════════════════════════════════ -->
    <div class="carousel-panel">
        <div
            id="carouselBackground"
            class="carousel slide carousel-fade h-100"
            data-bs-ride="carousel"
            data-bs-interval="5000"
        >
            <div class="carousel-inner h-100">
                <div
                    class="carousel-item active"
                    style="background-image: url('{{ asset('storage/imagenes/login.webp') }}');"
                ></div>
                <div
                    class="carousel-item"
                    style="background-image: url('{{ asset('storage/imagenes/undc2.webp') }}');"
                ></div>
                <div
                    class="carousel-item"
                    style="background-image: url('{{ asset('storage/imagenes/FUNDO-SAN-LUIS-4.webp') }}');"
                ></div>
                <div
                    class="carousel-item"
                    style="background-image: url('{{ asset('storage/imagenes/casa_cultura.webp') }}');"
                ></div>
            </div>
        </div>

        <!-- Overlay con tagline -->
        <div class="carousel-overlay">
            <div class="carousel-tagline">
                Gestión documental moderna y segura
                <span>Accede, organiza y controla todos los documentos institucionales en un solo lugar.</span>
            </div>
        </div>
    </div>

</body>
</html>