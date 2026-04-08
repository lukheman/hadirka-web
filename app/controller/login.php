<?php
$url = url();
// ===================== AUTO LOGIN DARI COOKIE =====================
if (!isset($_SESSION['id_user']) && isset($_COOKIE['remember_me']) && strpos($_COOKIE['remember_me'], ':') !== false) {
    list($selector, $validator) = explode(':', $_COOKIE['remember_me']);

    $stmt = $koneksi->prepare("SELECT * FROM tb_user_token WHERE selector = ? AND expires >= NOW()");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token = $result->fetch_assoc();
        if (hash_equals($token['validator_hash'], hash('sha256', $validator))) {
            $user_stmt = $koneksi->prepare("SELECT * FROM tb_user WHERE id_user = ?");
            $user_stmt->bind_param("i", $token['id_user']);
            $user_stmt->execute();
            $user_data = $user_stmt->get_result()->fetch_assoc();

            $_SESSION['id_user'] = $user_data['id_user'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['level'] = $user_data['level'];
            $_SESSION['login_time'] = time();

            // Jika safety, langsung ke master apd
            if ($_SESSION['level'] == 3) {
                header("Location: " . $url . "?page=master-apd");
            } else {
                header("Location: $url");
            }
            exit;
        }
    }
}

// ===================== LOGIN PROCESS =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $remember_me = isset($_POST['remember_me']);

    $query = $koneksi->prepare("SELECT * FROM tb_user WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        if (password_verify($password, $data['password'])) {
            // Hapus token lama
            $stmt_del = $koneksi->prepare("DELETE FROM tb_user_token WHERE id_user = ?");
            $stmt_del->bind_param("i", $data['id_user']);
            $stmt_del->execute();

            // Set session
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['site'] = $data['site'];
            $_SESSION['jabatan'] = $data['jabatan'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['level'] = $data['level'];
            $_SESSION['login_time'] = time();

            // Remember Me
            if ($remember_me) {
                $selector = bin2hex(random_bytes(6));
                $validator = bin2hex(random_bytes(32));
                $validator_hash = hash('sha256', $validator);
                $expires = date('Y-m-d H:i:s', strtotime('+6 months'));

                $stmt = $koneksi->prepare("INSERT INTO tb_user_token (id_user, selector, validator_hash, expires) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $data['id_user'], $selector, $validator_hash, $expires);
                $stmt->execute();

                setcookie('remember_me', $selector . ':' . $validator, [
                    'expires' => time() + (6*30*24*60*60),
                    'path' => '/',
                    'httponly' => true,
                    'secure' => true // ganti true jika pakai HTTPS
                ]);
            }

            // Jika safety, langsung ke master apd
            if ($_SESSION['level'] == 3) {
                header("Location: " . $url . "?page=master-apd");
            } else {
                header("Location: $url");
            }
            exit;
        } else {
            header("Location: login?error=wrongpass");
            exit;
        }
    } else {
        header("Location: login?error=nouser");
        exit;
    }
}
