<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);
    session_start();
}
require_once 'app/config/koneksi.php';
require_once 'app/config/function.php';
require_once 'app/controller/login.php'; // logic login ada di sini
?>
<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr"
      data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <title>Login || PT. TJS</title>

    <link rel="icon" type="image/x-icon" href="assets/img/logo/ipm-logo.png"/>
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css"/>
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css"/>
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css"/>
    <link rel="stylesheet" href="assets/css/demo.css"/>
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css"/>
    <link rel="stylesheet" href="assets/vendor/css/pages/page-auth.css"/>
    <link rel="stylesheet" href="assets/css/ipm-theme.css?v=<?= filemtime('assets/css/ipm-theme.css') ?>"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast/dist/css/iziToast.min.css">

    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/js/config.js"></script>
</head>
<body>
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card">
                <div class="card-body">
                    <div class="app-brand justify-content-center">
                        <a href="index.php" class="app-brand-link gap-2">
                            <img src="assets/img/logo/ipm-logo.png" width="280" style="max-width:100%;height:auto;" alt="">
                        </a>
                    </div>

                    <h4 class="mb-2">Selamat Datang! 👋</h4>
                    <p class="mb-4">Silahkan Masukkan username Dan Password anda</p>

                    <form id="formAuthentication" class="mb-3" action="" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Masukkan username disini" autofocus/>
                        </div>

                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">Password</label>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                       placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                       aria-describedby="password"/>
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            <a href="#"><small>Lupa password?</small></a>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me" name="remember_me"/>
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-primary d-grid w-100" type="submit">Masuk</button>
                        </div>
                    </form>

                    <p class="text-center">
                        <span>Belum ada akun?</span>
                        <a href="#"><span>Buat disini</span></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="assets/vendor/js/menu.js"></script>
<script src="assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/izitoast/dist/js/iziToast.min.js"></script>

<?php if(isset($_GET['error'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if($_GET['error'] == 'wrongpass'): ?>
        iziToast.error({title: 'Error', message: 'Password salah!'});
        <?php elseif($_GET['error'] == 'nouser'): ?>
        iziToast.error({title: 'Error', message: 'User tidak ditemukan!'});
        <?php endif; ?>
    });
</script>
<?php endif; ?>
</body>
</html>
