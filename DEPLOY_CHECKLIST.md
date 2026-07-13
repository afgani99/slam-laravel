# SLAM Production Deployment Checklist

Target server:
- OS: Ubuntu 24.04.1 LTS
- IP publik: 202.162.205.205
- Akses: SSH only (tidak ada console/langsung)
- Domain (pending sysadmin): slam.bdl.nusa.net.id
- Multi-app server: SLAM (Laravel 12) + app PHP lain menyusul
- Web server: Nginx dengan server block (virtual host) per app
- Path: /var/www/slam
- PHP: 8.3
- Database: MySQL 8
- Node: 20 LTS

App requirement dari repo:
- Laravel 12, PHP ^8.2
- Vite build (npm run build)
- SESSION/CACHE/QUEUE = database driver
- Tidak ada job dispatch aktif -> queue worker OPSIONAL
- Tidak ada scheduled task -> scheduler cron OPSIONAL
- Ada AdminUserSeeder -> perlu dijalankan sekali
- Composer package: phpoffice/phpspreadsheet -> butuh ext zip xml gd

Aturan emas SSH (karena akses cuma via SSH):
- Selalu allow OpenSSH SEBELUM ufw enable
- Selalu buka session SSH KEDUA sebelum ubah sshd_config
- Pakai systemctl reload ssh, jangan restart
- Jangan disable password auth sebelum SSH key terbukti bisa login
- Jangan disable root login sebelum user deploy terbukti bisa sudo


================================================================
FASE 0 — PRE-FLIGHT LOKAL
================================================================

[ ] 0.1 Pastikan branch produksi di repo sudah bersih dan siap deploy
[ ] 0.2 Pastikan .env.example sudah lengkap
[ ] 0.3 Pastikan AdminUserSeeder punya kredensial default yang aman (akan diganti setelah login pertama)
[ ] 0.4 Catat repository URL untuk git clone di server
[ ] 0.5 Siapkan SSH public key lokal (~/.ssh/id_ed25519.pub) untuk user deploy


================================================================
FASE 1 — AKSES SERVER AWAL
================================================================

[ ] 1.1 SSH ke server sebagai user yang diberikan sysadmin (root atau ubuntu)
    ssh <user>@202.162.205.205

[ ] 1.2 Update paket OS
    sudo apt update
    sudo apt upgrade -y

[ ] 1.3 Set timezone
    sudo timedatectl set-timezone Asia/Jakarta
    timedatectl

[ ] 1.4 Set hostname
    sudo hostnamectl set-hostname prod-app-01

[ ] 1.5 Install locale ID
    sudo apt install -y locales
    sudo locale-gen en_US.UTF-8 id_ID.UTF-8

[ ] 1.6 Install paket dasar
    sudo apt install -y curl wget git unzip zip ca-certificates gnupg lsb-release build-essential acl htop


================================================================
FASE 2 — USER DEPLOY (WAJIB SEBELUM SSH HARDENING)
================================================================

[ ] 2.1 Buat user deploy
    sudo adduser deploy

[ ] 2.2 Beri sudo
    sudo usermod -aG sudo deploy

[ ] 2.3 Buat direktori .ssh untuk deploy
    sudo mkdir -p /home/deploy/.ssh
    sudo chmod 700 /home/deploy/.ssh

[ ] 2.4 Copy SSH public key ke /home/deploy/.ssh/authorized_keys
    # dari lokal:
    ssh-copy-id deploy@202.162.205.205
    # atau manual: paste isi id_ed25519.pub ke authorized_keys

[ ] 2.5 Fix ownership dan permission
    sudo chown -R deploy:deploy /home/deploy/.ssh
    sudo chmod 600 /home/deploy/.ssh/authorized_keys

[ ] 2.6 TEST DARI TERMINAL BARU (jangan tutup session lama)
    ssh deploy@202.162.205.205
    sudo whoami   # harus keluar "root"

[ ] 2.7 Jika langkah 2.6 GAGAL, jangan lanjut. Perbaiki dulu.


================================================================
FASE 3 — FIREWALL (URUTAN KRITIKAL)
================================================================

[ ] 3.1 Install UFW
    sudo apt install -y ufw

[ ] 3.2 Cek status default (harus inactive)
    sudo ufw status

[ ] 3.3 Allow SSH DULU (WAJIB SEBELUM enable)
    sudo ufw allow OpenSSH

[ ] 3.4 Allow HTTP dan HTTPS
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp

[ ] 3.5 Baru enable firewall
    sudo ufw enable
    # ketik "y" saat ditanya

[ ] 3.6 Verifikasi
    sudo ufw status verbose

[ ] 3.7 TEST DARI TERMINAL BARU: masih bisa SSH?


================================================================
FASE 4 — SSH HARDENING (HATI-HATI!)
================================================================

Prasyarat: user deploy sudah terbukti bisa login dan sudo (Fase 2 selesai).

[ ] 4.1 Buka SESSION SSH KEDUA sebagai deploy (biarkan tetap terbuka)

[ ] 4.2 Backup config SSH
    sudo cp /etc/ssh/sshd_config /etc/ssh/sshd_config.bak

[ ] 4.3 Edit config
    sudo nano /etc/ssh/sshd_config

Ubah/tambahkan:
    PermitRootLogin no
    PasswordAuthentication no
    PubkeyAuthentication yes

[ ] 4.4 Test config
    sudo sshd -t
    # tidak boleh ada error

[ ] 4.5 Reload SSH (JANGAN restart)
    sudo systemctl reload ssh

[ ] 4.6 TEST DARI TERMINAL KETIGA: ssh deploy@202.162.205.205 masih bisa
    # session kedua masih terbuka sebagai rollback

[ ] 4.7 Jika gagal, di session kedua:
    sudo cp /etc/ssh/sshd_config.bak /etc/ssh/sshd_config
    sudo systemctl reload ssh


================================================================
FASE 5 — FAIL2BAN
================================================================

[ ] 5.1 Install
    sudo apt install -y fail2ban

[ ] 5.2 Copy jail
    sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

[ ] 5.3 Edit jail.local, pastikan sshd aktif
    sudo nano /etc/fail2ban/jail.local
    # cari [sshd], set:
    #   enabled = true
    #   maxretry = 5

[ ] 5.4 Enable dan start
    sudo systemctl enable --now fail2ban
    sudo systemctl status fail2ban
    sudo fail2ban-client status sshd

[ ] 5.5 Install unattended-upgrades
    sudo apt install -y unattended-upgrades
    sudo dpkg-reconfigure --priority=low unattended-upgrades


================================================================
FASE 6 — INSTALL STACK
================================================================

[ ] 6.1 Nginx
    sudo apt install -y nginx
    sudo systemctl enable --now nginx
    sudo systemctl status nginx

[ ] 6.2 Test Nginx via IP
    # dari browser lokal: http://202.162.205.205
    # harus muncul "Welcome to nginx!"

[ ] 6.3 PHP 8.3 + ekstensi Laravel
    sudo apt install -y \
      php8.3-fpm php8.3-cli php8.3-common \
      php8.3-mysql php8.3-mbstring php8.3-xml \
      php8.3-curl php8.3-zip php8.3-bcmath \
      php8.3-gd php8.3-intl php8.3-readline

[ ] 6.4 Enable PHP-FPM
    sudo systemctl enable --now php8.3-fpm
    sudo systemctl status php8.3-fpm
    php -v

[ ] 6.5 Composer
    cd /tmp
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    sudo mv composer.phar /usr/local/bin/composer
    composer --version

[ ] 6.6 Node.js 20 LTS
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
    node -v
    npm -v


================================================================
FASE 7 — DATABASE
================================================================

[ ] 7.1 Install MySQL
    sudo apt install -y mysql-server
    sudo systemctl enable --now mysql
    sudo systemctl status mysql

[ ] 7.2 Secure installation
    sudo mysql_secure_installation
    # - Set password root MySQL
    # - Remove anonymous users: y
    # - Disallow root remote: y
    # - Remove test db: y
    # - Reload privileges: y

[ ] 7.3 Buat database dan user
    sudo mysql
    -- di dalam MySQL:
    CREATE DATABASE slam_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'slam_user'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT_DI_SINI';
    GRANT ALL PRIVILEGES ON slam_production.* TO 'slam_user'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;

[ ] 7.4 Test login
    mysql -u slam_user -p slam_production

[ ] 7.5 Verifikasi bind hanya ke localhost
    sudo ss -tulpn | grep 3306
    # harus 127.0.0.1:3306


================================================================
FASE 8 — STRUKTUR DIREKTORI MULTI-APP
================================================================

Konvensi: setiap app punya folder sendiri di /var/www/<nama-app>

[ ] 8.1 Buat struktur
    sudo mkdir -p /var/www/slam

[ ] 8.2 Owner ke deploy:www-data
    sudo chown -R deploy:www-data /var/www/slam

[ ] 8.3 Set permission
    sudo chmod -R 775 /var/www/slam

[ ] 8.4 Tambahkan deploy ke grup www-data (untuk shared write)
    sudo usermod -aG www-data deploy
    # logout-login ulang deploy agar grup aktif


================================================================
FASE 9 — DEPLOY SOURCE CODE
================================================================

[ ] 9.1 Login sebagai deploy
    ssh deploy@202.162.205.205

[ ] 9.2 Clone repo
    cd /var/www
    git clone <URL_REPO_SLAM> slam
    cd slam

[ ] 9.3 Checkout branch produksi
    git checkout main
    git log --oneline -5

[ ] 9.4 Install composer dependency production
    composer install --no-dev --optimize-autoloader

[ ] 9.5 Install npm dependency dan build asset
    npm ci
    npm run build

[ ] 9.6 Verifikasi build
    ls -la public/build


================================================================
FASE 10 — KONFIGURASI .ENV PRODUKSI
================================================================

[ ] 10.1 Copy env
    cp .env.example .env

[ ] 10.2 Edit .env
    nano .env

Isi minimal:
    APP_NAME=SLAM
    APP_ENV=production
    APP_KEY=
    APP_DEBUG=false
    APP_URL=http://202.162.205.205
    # nanti diganti ke https://slam.bdl.nusa.net.id setelah DNS + SSL

    APP_LOCALE=id
    APP_FALLBACK_LOCALE=en
    APP_FAKER_LOCALE=id_ID

    LOG_CHANNEL=stack
    LOG_LEVEL=warning

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=slam_production
    DB_USERNAME=slam_user
    DB_PASSWORD=<password_dari_langkah_7.3>

    SESSION_DRIVER=database
    SESSION_LIFETIME=120
    SESSION_SECURE_COOKIE=false
    # ubah ke true setelah SSL aktif

    CACHE_STORE=database
    QUEUE_CONNECTION=database
    FILESYSTEM_DISK=local
    BROADCAST_CONNECTION=log

    MAIL_MAILER=log

[ ] 10.3 Generate key
    php artisan key:generate

[ ] 10.4 Amankan .env
    chmod 640 .env
    # owner sudah deploy, grup www-data via umask/chown

[ ] 10.5 Verifikasi Laravel
    php artisan about


================================================================
FASE 11 — PERMISSION LARAVEL
================================================================

[ ] 11.1 Set ownership project
    sudo chown -R deploy:www-data /var/www/slam

[ ] 11.2 Set permission
    sudo find /var/www/slam -type d -exec chmod 775 {} \;
    sudo find /var/www/slam -type f -exec chmod 664 {} \;

[ ] 11.3 Storage dan bootstrap/cache
    sudo chmod -R 775 /var/www/slam/storage /var/www/slam/bootstrap/cache
    sudo chown -R deploy:www-data /var/www/slam/storage /var/www/slam/bootstrap/cache

[ ] 11.4 (Opsional) ACL agar deploy dan www-data sama-sama bisa nulis
    sudo setfacl -R -m u:www-data:rwx -m u:deploy:rwx /var/www/slam/storage /var/www/slam/bootstrap/cache
    sudo setfacl -dR -m u:www-data:rwx -m u:deploy:rwx /var/www/slam/storage /var/www/slam/bootstrap/cache


================================================================
FASE 12 — MIGRATION + SEED
================================================================

[ ] 12.1 Cek status migrasi
    php artisan migrate:status

[ ] 12.2 Jalankan migrasi
    php artisan migrate --force

[ ] 12.3 Seed AdminUserSeeder untuk user awal
    php artisan db:seed --class=AdminUserSeeder --force

[ ] 12.4 Verifikasi tabel
    mysql -u slam_user -p -e "USE slam_production; SHOW TABLES;"

[ ] 12.5 (Opsional) Jika ingin restore dari database dev existing:
    # transfer file .sql ke server ke /tmp/backup.sql
    mysql -u slam_user -p slam_production < /tmp/backup.sql


================================================================
FASE 13 — STORAGE LINK + CACHE PRODUKSI
================================================================

[ ] 13.1 Symbolic link storage
    php artisan storage:link

[ ] 13.2 Clear cache lama
    php artisan optimize:clear

[ ] 13.3 Cache produksi
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize


================================================================
FASE 14 — NGINX SERVER BLOCK SLAM (IP-BASED DULU)
================================================================

[ ] 14.1 Buat server block
    sudo nano /etc/nginx/sites-available/slam

Isi:
    server {
        listen 80;
        listen [::]:80;

        # SEMENTARA: pakai IP; nanti ganti ke slam.bdl.nusa.net.id
        server_name 202.162.205.205 slam.bdl.nusa.net.id;

        root /var/www/slam/public;
        index index.php index.html;

        access_log /var/log/nginx/slam_access.log;
        error_log /var/log/nginx/slam_error.log;

        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";
        add_header Referrer-Policy "strict-origin-when-cross-origin";

        charset utf-8;
        client_max_body_size 20M;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }

[ ] 14.2 Enable site
    sudo ln -s /etc/nginx/sites-available/slam /etc/nginx/sites-enabled/slam

[ ] 14.3 Non-aktifkan default (karena SLAM jadi default sementara)
    sudo rm -f /etc/nginx/sites-enabled/default

[ ] 14.4 Test dan reload
    sudo nginx -t
    sudo systemctl reload nginx

[ ] 14.5 Test akses
    curl -I http://202.162.205.205
    # browser lokal: http://202.162.205.205


================================================================
FASE 15 — SMOKE TEST APLIKASI (VIA IP)
================================================================

[ ] 15.1 Buka http://202.162.205.205 di browser
[ ] 15.2 Login sebagai admin (kredensial dari AdminUserSeeder)
[ ] 15.3 Dashboard tampil
[ ] 15.4 Master CID: list, search, create
[ ] 15.5 Tickets: create, edit, set pending, resume, close, reopen (admin)
[ ] 15.6 GAMAS: create, tambah CID, pending/resume/close, sync ke child ticket
[ ] 15.7 Report SLA bulanan dan Restitusi tampil
[ ] 15.8 Export Excel jalan
[ ] 15.9 Settings: User Management, Language Switcher, Backup/Restore
[ ] 15.10 Ganti password admin default

[ ] 15.11 Cek log tidak ada error kritis
    tail -n 100 /var/www/slam/storage/logs/laravel.log
    sudo tail -n 100 /var/log/nginx/slam_error.log


================================================================
FASE 16 — KOORDINASI DNS DENGAN SYSADMIN
================================================================

[ ] 16.1 Minta sysadmin buat A record:
    slam.bdl.nusa.net.id  ->  202.162.205.205

[ ] 16.2 Tunggu propagasi, verifikasi:
    dig slam.bdl.nusa.net.id +short
    # harus keluar 202.162.205.205

[ ] 16.3 Test HTTP via domain
    curl -I http://slam.bdl.nusa.net.id


================================================================
FASE 17 — SSL LET'S ENCRYPT (SETELAH DNS AKTIF)
================================================================

Prasyarat: Fase 16 selesai (DNS sudah mengarah ke IP server).

[ ] 17.1 Install Certbot
    sudo apt install -y certbot python3-certbot-nginx

[ ] 17.2 Request sertifikat + auto-config Nginx
    sudo certbot --nginx -d slam.bdl.nusa.net.id
    # - Masukkan email valid
    # - Setujui TOS
    # - Pilih "Redirect" (2) agar HTTP -> HTTPS otomatis

[ ] 17.3 Test auto-renewal
    sudo certbot renew --dry-run

[ ] 17.4 Cek Nginx
    sudo nginx -t
    sudo systemctl reload nginx

[ ] 17.5 Test HTTPS
    curl -I https://slam.bdl.nusa.net.id

[ ] 17.6 Update .env
    nano /var/www/slam/.env
    # ubah:
    #   APP_URL=https://slam.bdl.nusa.net.id
    #   SESSION_SECURE_COOKIE=true

[ ] 17.7 Cache ulang
    cd /var/www/slam
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache


================================================================
FASE 18 — BACKUP DATABASE
================================================================

[ ] 18.1 Buat direktori backup
    sudo mkdir -p /var/backups/slam/database
    sudo chown -R deploy:deploy /var/backups/slam

[ ] 18.2 Buat script backup
    nano /home/deploy/backup-slam-db.sh

Isi:
    #!/usr/bin/env bash
    set -euo pipefail
    DATE=$(date +"%Y%m%d_%H%M%S")
    BACKUP_DIR="/var/backups/slam/database"
    DB_NAME="slam_production"
    DB_USER="slam_user"
    DB_PASS="<PASSWORD>"
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"
    find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +14 -delete

[ ] 18.3 Amankan
    chmod 700 /home/deploy/backup-slam-db.sh

[ ] 18.4 Test manual
    /home/deploy/backup-slam-db.sh
    ls -la /var/backups/slam/database

[ ] 18.5 Cron harian 02:00
    crontab -e
    # tambahkan:
    0 2 * * * /home/deploy/backup-slam-db.sh >> /home/deploy/backup-slam-db.log 2>&1


================================================================
FASE 19 — SCHEDULER & QUEUE (OPSIONAL, SESUAI KEBUTUHAN)
================================================================

Catatan: repo SLAM saat ini TIDAK menggunakan scheduled task maupun
job dispatch aktif. Dua fase ini boleh diskip sampai fitur tersebut
ditambahkan.

[ ] 19.1 (Skip sekarang) Scheduler cron
    # Nanti saat butuh:
    crontab -e
    * * * * * cd /var/www/slam && php artisan schedule:run >> /dev/null 2>&1

[ ] 19.2 (Skip sekarang) Queue worker via Supervisor
    # Nanti saat butuh:
    sudo apt install -y supervisor
    sudo nano /etc/supervisor/conf.d/slam-worker.conf


================================================================
FASE 20 — POST-DEPLOY VERIFIKASI FINAL
================================================================

[ ] 20.1 APP_ENV=production, APP_DEBUG=false
    grep -E "APP_ENV|APP_DEBUG" /var/www/slam/.env

[ ] 20.2 .env tidak bisa diakses publik
    curl -I https://slam.bdl.nusa.net.id/.env
    # harus 403 atau 404, bukan 200

[ ] 20.3 Directory listing tidak aktif
    curl -I https://slam.bdl.nusa.net.id/storage/

[ ] 20.4 SSL grade
    # cek di https://www.ssllabs.com/ssltest/ (target A/A+)

[ ] 20.5 Semua fitur SLAM di Fase 15 diulang via domain HTTPS

[ ] 20.6 Log bersih
    tail -n 200 /var/www/slam/storage/logs/laravel.log
    sudo tail -n 200 /var/log/nginx/slam_error.log

[ ] 20.7 Backup DB pernah berhasil (Fase 18)
[ ] 20.8 Firewall aktif, hanya 22/80/443 terbuka
    sudo ufw status
[ ] 20.9 Fail2Ban aktif
    sudo fail2ban-client status sshd
[ ] 20.10 Ganti semua password default (admin app, MySQL user, dsb.)


================================================================
LAMPIRAN A — PROSEDUR UPDATE APLIKASI KE DEPAN
================================================================

    ssh deploy@slam.bdl.nusa.net.id
    cd /var/www/slam
    php artisan down
    git pull origin main
    composer install --no-dev --optimize-autoloader
    npm ci
    npm run build
    php artisan migrate --force
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan up
    tail -n 50 storage/logs/laravel.log


================================================================
LAMPIRAN B — MENAMBAH APP BARU DI SERVER YANG SAMA
================================================================

Setiap app baru:
1. Buat folder: /var/www/<nama-app> dengan owner deploy:www-data
2. Deploy source code ke sana
3. (Jika Laravel) install composer + build asset + migrate
4. Buat server block Nginx baru di /etc/nginx/sites-available/<nama-app>
   - Sub-domain berbeda: server_name app2.bdl.nusa.net.id;
   - Atau port berbeda sementara: listen 8081;
5. Symlink ke sites-enabled, nginx -t, reload
6. Minta sysadmin pointing DNS jika pakai sub-domain
7. Certbot untuk SSL app baru

Prinsip:
- 1 user DB berbeda per aplikasi
- 1 .env berbeda per aplikasi
- 1 log Nginx berbeda per aplikasi
- Jangan pernah pakai user MySQL root untuk aplikasi
