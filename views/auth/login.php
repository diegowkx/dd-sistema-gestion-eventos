<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head.php'; ?>

    <!-- CSS Exclusivo del Login -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom/views/login.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/custom/views/login.css'); ?>">
</head>
<body class="font-primary-family login-body">

    <div class="login-wrapper">
        <!-- Fondo Superior -->
        <div class="login-top-section">
            <div class="brand-welcome">
                <h1 class="text-bold"><?php echo APP_NAME; ?></h1>
                <p>Sistema de Gestión Inmobiliaria</p>
            </div>
            
            <!-- Separador SVG Ondulado -->
            <div class="wave-separator">
                <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="#f3e9dc" fill-opacity="1" d="M0,160L48,176C96,192,192,224,288,218.7C384,213,480,171,576,165.3C672,160,768,192,864,202.7C960,213,1056,203,1152,181.3C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>
        </div>

        <!-- Fondo Inferior -->
        <div class="login-bottom-section">
            <div class="login-card-box">
                <div class="login-header text-center">
                    <a href="index.php" class="brand-logo text-bold">
                        <?php echo APP_NAME; ?>
                    </a>
                    <h4 class="login-title text-bold">Iniciar Sesión</h4>
                </div>

                <form id="loginForm" action="<?php echo BASE_URL; ?>/?c=auth&a=process_login" method="POST" class="login-form">
                    <!-- Campo de Correo Electrónico -->
                    <div class="form-group mb-3">
                        <label for="emailInput" class="text-bold mb-1">Correo electrónico</label>
                        <input type="email" class="form-control custom-input" id="emailInput" name="email" placeholder="tucorreo@example.com" required autocomplete="email">
                    </div>

                    <!-- Campo de Contraseña Encapsulado -->
                    <div class="form-group mb-4">
                        <label for="passwordInput" class="text-bold mb-1">Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control custom-input" id="passwordInput" name="password" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" id="togglePassword" class="password-toggle-btn" tabindex="-1">
                                <i class="fa fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-action">
                        <button type="submit" class="btn btn-custom-submit text-bold">Ingresar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 1. Scripts globales -->
    <?php include __DIR__ . '/../layouts/scripts.php'; ?>

    <!-- 2. Alertas dinámicas con SweetAlert2 -->
    <?php if (isset($error) && $error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: '¡Acceso Denegado!',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: (typeof AppConfig !== 'undefined' && AppConfig.sweetAlertDefaults) 
                ? AppConfig.sweetAlertDefaults.confirmButtonColor 
                : '#d9383a',
            background: '#ffffff',
            customClass: { popup: 'shadow-lg' },
            heightAuto: false
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Completado!',
            text: '<?php echo addslashes($success); ?>',
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            heightAuto: false
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
    <?php endif; ?>

    <!-- 3. JS Exclusivo del Login -->
    <?php 
    $loginJsPath = __DIR__ . '/../../assets/js/custom/views/login.js';
    if (file_exists($loginJsPath)) {
        echo '<script src="' . BASE_URL . '/assets/js/custom/views/login.js?v=' . filemtime($loginJsPath) . '"></script>' . PHP_EOL;
    }
    
    $viewLoginJsPath = __DIR__ . '/../../views/auth/login.js';
    if (file_exists($viewLoginJsPath)) {
        echo '<script src="' . BASE_URL . '/views/auth/login.js?v=' . filemtime($viewLoginJsPath) . '"></script>' . PHP_EOL;
    }
    ?>
</body>
</html>