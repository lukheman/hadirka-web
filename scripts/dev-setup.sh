#!/usr/bin/env bash
# scripts/dev-setup.sh — Setup development sekali jalan (MySQL/MariaDB lokal via PDO).
# Bukan SQLite: codebase memakai sintaks khusus MySQL sehingga
# SQLite tidak kompatibel (lihat app/config/database.php).
set -euo pipefail
cd "$(dirname "$0")/.."

echo "== HRIS dev-setup =="

if [[ ! -f .env ]]; then
  echo "[1/5] .env tidak ada -> salin dari .env.example"
  cp .env.example .env
else
  echo "[1/5] .env sudah ada, dipakai apa adanya"
fi

echo "[2/5] Cek ekstensi PHP"
php -m | grep -qi pdo_mysql || { echo "ERROR: ekstensi php pdo_mysql belum terinstal"; exit 1; }
php app/config/check-db.php 2>/dev/null || php -r 'require "app/config/database.php"; $c = db_config(); echo "APP_ENV dev: " . ($c["is_dev"] ? "ya" : "tidak") . ", DB: " . $c["name"] . "@" . $c["host"] . PHP_EOL;'

echo "[3/5] Koneksi + auto-create database (development)"
php -r "require 'app/config/koneksi.php'; echo 'Koneksi OK (' . get_class(\$koneksi) . '): ' . \$koneksi->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;"

echo "[4/5] Migrasi database"
if [[ -x vendor/bin/phinx ]]; then
  vendor/bin/phinx migrate
else
  echo "SKIP: vendor/bin/phinx tidak ada, jalankan 'composer install' dulu"
fi

echo "[5/5] Folder upload writable"
mkdir -p app/uploads/{profil,template-kontrak,kontrak,cuti,izin,pinjaman,sks,apd,bukti_transaksi} assets/img/user-pic
chmod -R 775 app/uploads assets/img/user-pic || true

echo ""
echo "Selesai. Jalankan:"
echo "  php -S localhost:8000 router.php"
echo "Lalu buka http://localhost:8000/login.php"
