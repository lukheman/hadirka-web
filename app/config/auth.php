<?php
session_start();
if (
    empty($_SESSION['id_user']) &&
    empty($_SESSION['username']) &&
    empty($_SESSION['level'])
) {
    // Redirect paksa
    header("Location: login?error=session_invalid");
    exit;
}
