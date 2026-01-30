<?php
session_start(); 
$isLoggedIn = isset($_SESSION['user_id']); 

// Include database
include_once '../connection/connect.php';
$pdo = getDatabaseConnection();

// User Info
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmtUser = $pdo->prepare("SELECT name FROM users WHERE user_id = :user_id LIMIT 1");
    $stmtUser->execute(['user_id' => $userId]);
    $user = $stmtUser->fetch();
    $userName = $user ? htmlspecialchars($user['name']) : 'Pengguna';
} else {
    $userName = null;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - BÉLI TIKÉT</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="../css/navbar_footer.css">
    <link rel="stylesheet" type="text/css" href="../css/tentang_kami.css">
</head>

<body>
    
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <div class="mx-auto my-3 my-lg-0 w-100 w-lg-50 px-lg-4">
                        <form action="../jelajah.php" method="get" class="position-relative">
                            <input type="text" name="search" class="form-control search-bar" placeholder="Cari event..." readonly onclick="window.location.href='../jelajah.php'">
                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle" type="button" style="background: #ff6b6b; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class="bi bi-search text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                    </div>

                    <div class="d-flex align-items-center gap-3 justify-content-end">
                        <a href="../jelajah.php" class="nav-link">Jelajah</a>
                        <?php if (!$isLoggedIn): ?>
                            <a href="register.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">Daftar</a>
                            <a href="login.php" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold border-0">Masuk</a>
                        <?php else: ?>
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                                    <div class="profile-icon">
                                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                                    <li><h6 class="dropdown-header">Halo, <?php echo htmlspecialchars($userName); ?></h6></li>
                                    <li><a class="dropdown-item" href="riwayat.php">Tiket Saya</a></li>
                                    <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Keluar</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">Tentang Kami</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
                    <li class="breadcrumb-item active text-white opacity-75" aria-current="page">Tentang Kami</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container pb-5">
        
        <div class="about-text-block">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="../img/group/Foto_Formal.jpeg" onerror="this.style.display='none'" class="img-fluid mb-4 mb-lg-0" alt="About Illustration">
                    </div>
                <div class="col-lg-6">
                    <h2 class="mb-4">Cerita Kami</h2>
                    <div class="about-content">
                        <?php
                        $heroFile = '../file_txt/hero_text.txt';
                        if (file_exists($heroFile)) {
                            echo nl2br(file_get_contents($heroFile));
                        } else {
                            echo "<p>BÉLI TIKÉT adalah platform penjualan tiket event terdepan yang menghubungkan event creator dengan audiens mereka. Kami percaya bahwa setiap momen berharga layak dirayakan.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 text-center mb-4">
                <h2 class="fw-bold">Mengapa Memilih Kami?</h2>
                <p class="text-muted">Kami memberikan pengalaman terbaik untuk setiap event Anda.</p>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h5 class="feature-title">Transaksi Aman</h5>
                    <p class="feature-desc">Sistem pembayaran terintegrasi dengan keamanan tingkat tinggi untuk kenyamanan Anda.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
                    <h5 class="feature-title">Proses Cepat</h5>
                    <p class="feature-desc">Pesan tiket dalam hitungan detik. E-ticket langsung dikirim ke email Anda.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-headset"></i></div>
                    <h5 class="feature-title">Layanan 24/7</h5>
                    <p class="feature-desc">Tim support kami siap membantu kendala pemesanan Anda kapan saja.</p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center">
                    <?php
                    $contentFile = '../file_txt/content_text.txt';
                    if (file_exists($contentFile)) {
                        echo nl2br(file_get_contents($contentFile));
                    }
                    ?>
                </div>
            </div>
        </div>

    </div>

    <footer>
        <div class="footer">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6">
                        <h4 class="mb-3">BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.</h4>
                        <p>Platform pembelian tiket event terpercaya.</p>
                        <div class="social-icons mt-4">
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i></a>
                            <a href="#"><i class="bi bi-facebook"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="mb-3">Navigasi</h5>
                        <ul>
                            <li><a href="../jelajah.php">Jelajah</a></li>
                            <li><a href="tentang_kami.php">Tentang Kami</a></li>
                            <li><a href="#">Blog</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="mb-3">Bantuan</h5>
                        <ul>
                            <li><a href="#">Pusat Bantuan</a></li>
                            <li><a href="#">Kebijakan Privasi</a></li>
                            <li><a href="#">Syarat & Ketentuan</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="mb-3">Pembayaran</h5>
                        <div class="p-3 bg-white rounded-3 d-inline-block">
                            <img src="../assets/images/logo_bsi.png" alt="Bank" style="height: 25px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <div class="container">
                &copy; 2024 PT Global Loket Sejahtera.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/navbar.js"></script>
</body>
</html>