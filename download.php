<?php
$apkFile = __DIR__ . '/ipm-absen.apk';
$apkAvailable = is_file($apkFile) && is_readable($apkFile);
$apkSize = $apkAvailable ? number_format(filesize($apkFile) / 1048576, 1, ',', '.') . ' MB' : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Download aplikasi HRIS-IPM untuk presensi dan layanan karyawan.">
    <title>Download Aplikasi | HRIS-IPM</title>
    <link rel="icon" type="image/png" href="assets/img/logo/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #1d5c96;
            --blue-dark: #123f68;
            --orange: #f2921d;
            --orange-dark: #d97706;
            --ink: #142235;
            --muted: #64748b;
            --line: #e6edf4;
            --surface: #ffffff;
            --background: #f7fafc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background:
                radial-gradient(circle at 85% 8%, rgba(242, 146, 29, .15), transparent 28rem),
                radial-gradient(circle at 0% 100%, rgba(29, 92, 150, .10), transparent 32rem),
                var(--background);
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
        }

        a { color: inherit; text-decoration: none; }

        .page-shell { width: min(1120px, calc(100% - 40px)); margin: 0 auto; }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 26px 0;
        }

        .brand { display: inline-flex; align-items: center; gap: 12px; }
        .brand img { width: 48px; height: 48px; object-fit: contain; }
        .brand-text { line-height: 1.15; }
        .brand-name { display: block; color: var(--blue-dark); font-size: 17px; font-weight: 800; letter-spacing: -.03em; }
        .brand-subtitle { display: block; margin-top: 4px; color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .login-link { color: var(--blue); font-size: 13px; font-weight: 700; }
        .login-link:hover { color: var(--orange-dark); }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(340px, .9fr);
            align-items: center;
            gap: 70px;
            min-height: 575px;
            padding: 44px 0 72px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border: 1px solid rgba(242, 146, 29, .25);
            border-radius: 999px;
            color: var(--orange-dark);
            background: rgba(242, 146, 29, .10);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--orange);
            content: '';
        }

        h1 { max-width: 650px; margin: 22px 0 18px; color: var(--blue-dark); font-size: clamp(36px, 5vw, 62px); line-height: 1.08; letter-spacing: -.055em; }
        h1 em { color: var(--orange); font-style: normal; }
        .intro { max-width: 570px; margin: 0; color: var(--muted); font-size: 16px; line-height: 1.8; }

        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 15px; margin-top: 32px; }
        .download-button {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            padding: 15px 20px;
            border-radius: 12px;
            color: #fff;
            background: var(--orange);
            box-shadow: 0 12px 24px rgba(242, 146, 29, .25);
            font-size: 14px;
            font-weight: 800;
            transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .download-button:hover { color: #fff; background: var(--orange-dark); box-shadow: 0 15px 28px rgba(217, 119, 6, .28); transform: translateY(-2px); }
        .download-button svg { width: 19px; height: 19px; }
        .version { color: var(--muted); font-size: 11px; font-weight: 600; }

        .benefits { display: flex; flex-wrap: wrap; gap: 18px 25px; margin-top: 35px; padding: 0; list-style: none; }
        .benefits li { display: flex; align-items: center; gap: 8px; color: var(--blue-dark); font-size: 12px; font-weight: 700; }
        .check { display: grid; width: 20px; height: 20px; place-items: center; border-radius: 50%; color: #fff; background: var(--blue); font-size: 12px; }

        .phone-stage { position: relative; display: grid; min-height: 440px; place-items: center; }
        .phone-stage::before, .phone-stage::after { position: absolute; border-radius: 50%; content: ''; }
        .phone-stage::before { width: 310px; height: 310px; background: rgba(29, 92, 150, .10); }
        .phone-stage::after { right: 8%; bottom: 16%; width: 72px; height: 72px; border: 13px solid rgba(242, 146, 29, .22); }
        .phone {
            position: relative;
            z-index: 1;
            width: 226px;
            padding: 10px;
            border: 7px solid #173b5f;
            border-radius: 34px;
            background: #173b5f;
            box-shadow: 20px 25px 45px rgba(18, 63, 104, .22), -10px 15px 30px rgba(29, 92, 150, .10);
            transform: rotate(5deg);
        }
        .phone-screen { overflow: hidden; min-height: 400px; border-radius: 23px; background: linear-gradient(155deg, #f8fbff 0%, #e9f3fb 100%); }
        .phone-notch { width: 76px; height: 17px; margin: 0 auto 18px; border-radius: 0 0 12px 12px; background: #173b5f; }
        .app-screen { padding: 0 16px 20px; }
        .app-header { display: flex; align-items: center; justify-content: space-between; color: var(--blue-dark); font-size: 10px; font-weight: 800; }
        .app-avatar { width: 27px; height: 27px; border: 3px solid #fff; border-radius: 50%; background: var(--orange); }
        .welcome { margin-top: 28px; color: var(--muted); font-size: 9px; }
        .welcome strong { display: block; margin-top: 5px; color: var(--blue-dark); font-size: 16px; }
        .attendance-card { margin-top: 18px; padding: 18px 14px; border-radius: 15px; color: #fff; background: linear-gradient(135deg, var(--blue) 0%, #2e7bb8 100%); box-shadow: 0 10px 20px rgba(29, 92, 150, .20); }
        .attendance-card small { opacity: .76; font-size: 8px; }
        .attendance-card strong { display: block; margin: 7px 0 15px; font-size: 22px; }
        .attendance-action { display: block; padding: 8px; border-radius: 8px; color: var(--blue-dark); background: #fff; font-size: 9px; font-weight: 800; text-align: center; }
        .screen-label { margin-top: 18px; color: var(--muted); font-size: 8px; font-weight: 700; }
        .screen-row { display: flex; align-items: center; gap: 8px; margin-top: 9px; padding: 10px; border-radius: 10px; background: #fff; color: var(--blue-dark); font-size: 8px; font-weight: 700; box-shadow: 0 5px 12px rgba(29, 92, 150, .06); }
        .row-icon { width: 18px; height: 18px; border-radius: 6px; background: var(--orange); }

        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; padding: 31px 0 46px; border-top: 1px solid var(--line); }
        .step { display: flex; gap: 13px; }
        .step-number { display: grid; flex: 0 0 34px; width: 34px; height: 34px; place-items: center; border-radius: 10px; color: #fff; background: var(--blue); font-size: 13px; font-weight: 800; }
        .step h2 { margin: 2px 0 6px; color: var(--blue-dark); font-size: 13px; }
        .step p { margin: 0; color: var(--muted); font-size: 11px; line-height: 1.6; }

        .unavailable { display: inline-block; padding: 15px 20px; border-radius: 12px; color: #9a3412; background: #fff1e6; font-size: 13px; font-weight: 700; }
        footer { padding: 0 0 30px; color: #8b9aad; font-size: 11px; text-align: center; }

        @media (max-width: 800px) {
            .hero { grid-template-columns: 1fr; gap: 20px; padding-top: 25px; }
            .hero-copy { order: 1; }
            .phone-stage { order: 2; min-height: 370px; }
            .phone { transform: rotate(3deg) scale(.88); }
            .steps { grid-template-columns: 1fr; gap: 21px; }
        }

        @media (max-width: 480px) {
            .page-shell { width: min(100% - 28px, 1120px); }
            .topbar { padding: 18px 0; }
            .brand img { width: 40px; height: 40px; }
            .brand-name { font-size: 14px; }
            .brand-subtitle { font-size: 8px; }
            .login-link { font-size: 11px; }
            h1 { font-size: 38px; }
            .intro { font-size: 14px; }
            .actions { align-items: flex-start; flex-direction: column; }
            .phone-stage { min-height: 350px; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a href="download.php" class="brand" aria-label="HRIS-IPM">
                <img src="assets/img/logo/ipm-logo.png" alt="Logo IPM">
                <span class="brand-text">
                    <span class="brand-name">HRIS-IPM</span>
                    <span class="brand-subtitle">Employee app</span>
                </span>
            </a>
        </header>

        <main>
            <section class="hero">
                <div class="hero-copy">
                    <span class="eyebrow">Aplikasi resmi karyawan</span>
                    <h1>Semua kebutuhan kerja, <em>dalam genggaman.</em></h1>
                    <p class="intro">Gunakan aplikasi HRIS-IPM untuk presensi berbasis lokasi, pengajuan izin dan cuti, melihat slip gaji, serta menerima informasi perusahaan.</p>
                    <div class="actions">
                        <?php if ($apkAvailable): ?>
                            <a class="download-button" href="ipm-absen.apk" download="HRIS-IPM.apk">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M5 21h14"></path>
                                </svg>
                                Download Aplikasi Android
                            </a>
                            <span class="version">APK &middot; <?= htmlspecialchars($apkSize, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>
                            <span class="unavailable">File aplikasi belum tersedia</span>
                        <?php endif; ?>
                    </div>
                    <ul class="benefits" aria-label="Keunggulan aplikasi">
                        <li><span class="check">✓</span> Presensi GPS</li>
                        <li><span class="check">✓</span> Slip gaji digital</li>
                        <li><span class="check">✓</span> Pengajuan online</li>
                    </ul>
                </div>

                <div class="phone-stage" aria-hidden="true">
                    <div class="phone">
                        <div class="phone-screen">
                            <div class="phone-notch"></div>
                            <div class="app-screen">
                                <div class="app-header"><span>HRIS-IPM</span><span class="app-avatar"></span></div>
                                <div class="welcome">Selamat datang kembali,</div>
                                <strong class="welcome">Karyawan IPM</strong>
                                <div class="attendance-card">
                                    <small>STATUS KEHADIRAN HARI INI</small>
                                    <strong>Belum absen</strong>
                                    <span class="attendance-action">Absen sekarang</span>
                                </div>
                                <div class="screen-label">Menu utama</div>
                                <div class="screen-row"><span class="row-icon"></span> Riwayat kehadiran</div>
                                <div class="screen-row"><span class="row-icon"></span> Pengajuan izin &amp; cuti</div>
                                <div class="screen-row"><span class="row-icon"></span> Slip gaji</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="steps" aria-label="Cara instalasi">
                <div class="step">
                    <span class="step-number">01</span>
                    <div><h2>Download APK</h2><p>Tekan tombol download dari halaman ini menggunakan perangkat Android.</p></div>
                </div>
                <div class="step">
                    <span class="step-number">02</span>
                    <div><h2>Izinkan instalasi</h2><p>Buka file APK, lalu izinkan instalasi dari sumber ini jika diminta.</p></div>
                </div>
                <div class="step">
                    <span class="step-number">03</span>
                    <div><h2>Mulai gunakan</h2><p>Buka aplikasi HRIS-IPM dan masuk memakai akun karyawan Anda.</p></div>
                </div>
            </section>
        </main>

        <footer>&copy; <?= date('Y') ?> PT Indoteknik Prima Mekongga &middot; HRIS-IPM</footer>
    </div>
</body>
</html>
