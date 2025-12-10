<?php
require_once 'config/config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Sistem Point of Sale Modern</title>
    <link rel="stylesheet" href="./src/output.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #000000;
            color: #333;
            overflow-x: hidden;
        }

        .fade-in {
            animation: fadeIn .6s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Navigation */
        .navbar {
            background: #000000;
            border-bottom: 2px solid #d4af37;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #d4af37;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #d4af37;
        }

        .btn-login {
            background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%);
            color: #000000;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.5);
        }

        /* Hero Section */
        .hero {
            background: #000000;
            color: white;
            padding: 150px 2rem 100px;
            text-align: center;
            margin-top: 60px;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: #ffffff;
            animation: fadeInUp 1s ease;
        }

        .hero h1 span {
            color: #d4af37;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            color: #cccccc;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4af37 0%, #c9a961 100%);
            color: #000000;
            padding: 1rem 2.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.5);
        }

        .btn-secondary {
            background: transparent;
            color: #d4af37;
            padding: 1rem 2.5rem;
            border: 2px solid #d4af37;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: #d4af37;
            color: #000000;
            transform: translateY(-3px);
        }

        /* Features Section */
        .features {
            padding: 80px 2rem;
            background: #000000;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #d4af37;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: #ffffff;
            border: 2px solid #d4af37;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #000000;
        }

        .feature-card p {
            color: #666;
            line-height: 1.8;
        }

        /* Stats Section */
        .stats {
            background: #000000;
            border-top: 2px solid #d4af37;
            border-bottom: 2px solid #d4af37;
            color: white;
            padding: 60px 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            color: #d4af37;
        }

        .stat-item p {
            font-size: 1.1rem;
            color: #cccccc;
        }

        /* CTA Section */
        .cta {
            padding: 80px 2rem;
            background: #ffffff;
            border: 2px solid #d4af37;
            margin: 40px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #000000;
        }

        .cta h2 span {
            color: #d4af37;
        }

        .cta p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: #000000;
            border-top: 2px solid #d4af37;
            color: white;
            padding: 40px 2rem;
            text-align: center;
        }

        .footer p {
            color: #cccccc;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo"><?php echo APP_NAME; ?></a>
            <div class="nav-links">
                <a href="#features">Fitur</a>
                <a href="#about">Tentang</a>
                <a href="login.php" class="btn-login">Masuk</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Kelola <span>Toko Parfum</span> Anda dengan Mudah</h1>
            <p>Sistem Point of Sale modern yang membantu Anda mengelola penjualan, stok, dan laporan dengan efisien</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary">Mulai Sekarang</a>
                <a href="#features" class="btn-secondary">Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Fitur Unggulan</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h3>Point of Sale</h3>
                    <p>Sistem kasir modern dengan interface yang mudah digunakan. Proses transaksi cepat dan efisien dengan fitur pencarian parfum otomatis.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Laporan Lengkap</h3>
                    <p>Laporan penjualan harian, bulanan, dan tahunan. Analisis data yang akurat untuk membantu pengambilan keputusan bisnis.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Manajemen Stok</h3>
                    <p>Kelola stok parfum dengan mudah. Sistem alert untuk stok menipis dan manajemen pembelian dari supplier.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Multi User</h3>
                    <p>Sistem dengan 3 level akses: Admin, Kasir, dan Gudang. Setiap user memiliki hak akses sesuai perannya.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎁</div>
                    <h3>Diskon & PPN</h3>
                    <p>Fitur diskon fleksibel dan perhitungan PPN otomatis. Struk transaksi yang lengkap dan profesional.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Pembayaran Mudah</h3>
                    <p>Proses pembayaran yang cepat dengan perhitungan kembalian otomatis. Dukungan untuk berbagai metode pembayaran.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Akurat</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Tersedia</p>
                </div>
                <div class="stat-item">
                    <h3>3</h3>
                    <p>Level Akses</p>
                </div>
                <div class="stat-item">
                    <h3>∞</h3>
                    <p>Transaksi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="about">
        <div class="cta">
            <h2>Siap <span>Memulai?</span></h2>
            <p>Bergabunglah dengan sistem POS modern yang akan membantu bisnis toko parfum Anda berkembang</p>
            <a href="login.php" class="btn-primary">Login Sekarang</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">Dibuat dengan ❤️ untuk kemudahan bisnis Anda</p>
        </div>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
