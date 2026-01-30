<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Periksa Login
$isLoggedIn = isset($_SESSION['user_id']); 

// Koneksi Database
include_once '../connection/connect.php';

try {
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

    // Get Event Detail
    $event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

    if ($event_id) {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) die('Event tidak ditemukan');
    } else {
        die('Event tidak ditentukan');
    }
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - Beli Tiket</title>

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="../css/navbar_footer.css">
    <link rel="stylesheet" type="text/css" href="../css/tiket-page.css">
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
                            <input type="text" name="search" class="form-control search-bar" placeholder="Cari event lain..." readonly>
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

    <div class="container py-5">
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="../jelajah.php" class="text-decoration-none text-muted">Jelajah</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Event</li>
            </ol>
        </nav>

        <div class="row gx-5">
            
            <div class="col-lg-8 mb-5">
                
                <div class="event-header-wrapper">
                    <img src="<?= htmlspecialchars('../' . $event['event_image_path']) ?>" class="event-banner" alt="Banner Event">
                    <?php if(isset($event['event_type'])): ?>
                        <div class="event-badge text-uppercase">
                            <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($event['event_type']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h1 class="fw-bold mb-4 display-5"><?= htmlspecialchars($event['title']) ?></h1>

                <ul class="nav nav-tabs" id="eventTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="deskripsi-tab" data-bs-toggle="tab" data-bs-target="#deskripsi" type="button" role="tab">
                            Deskripsi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="detail-event-tab" data-bs-toggle="tab" data-bs-target="#detail-event" type="button" role="tab">
                            Informasi Tambahan
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="eventTabContent">
                    
                    <div class="tab-pane fade show active" id="deskripsi" role="tabpanel">
                        <div class="event-description">
                            <?= nl2br(htmlspecialchars($event['description'])) ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="detail-event" role="tabpanel">
                        <ul class="list-unstyled detail-list">
                            <?php if(!empty($event['dress_code'])): ?>
                            <li>
                                <i class="bi bi-person-badge"></i>
                                <div>
                                    <span class="fw-bold d-block text-dark">Dress Code</span>
                                    <span><?= htmlspecialchars($event['dress_code']) ?></span>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if(!empty($event['min_age'])): ?>
                            <li>
                                <i class="bi bi-exclamation-circle"></i>
                                <div>
                                    <span class="fw-bold d-block text-dark">Batasan Usia</span>
                                    <span>Minimal <?= htmlspecialchars($event['min_age']) ?> tahun</span>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <?php if(!empty($event['facilities'])): ?>
                            <li>
                                <i class="bi bi-stars"></i>
                                <div>
                                    <span class="fw-bold d-block text-dark">Fasilitas</span>
                                    <span><?= htmlspecialchars($event['facilities']) ?></span>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <div class="purchase-card">
                        <h4 class="fw-bold mb-4">Jadwal & Lokasi</h4>
                        
                        <div class="event-meta-item">
                            <div class="meta-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Tanggal</small>
                                <div class="fw-bold fs-5"><?= date("d M Y", strtotime($event['event_date'])) ?></div>
                                </div>
                        </div>

                        <div class="event-meta-item">
                            <div class="meta-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Lokasi</small>
                                <div class="fw-bold"><?= htmlspecialchars($event['location']) ?></div>
                                <a href="https://maps.google.com/?q=<?= urlencode($event['location']) ?>" target="_blank" class="text-decoration-none small" style="color: #ff6b6b;">Lihat di Peta</a>
                            </div>
                        </div>

                        <hr class="opacity-25 my-4">

                        <button class="btn btn-gradient shadow-lg" onclick="window.location.href='detail-tiket.php?event_id=<?= $event['event_id'] ?>'">
                            Beli Tiket Sekarang <i class="bi bi-arrow-right ms-2"></i>
                        </button>

                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="bi bi-building-fill"></i>
                            </div>
                            <div class="overflow-hidden">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Diselenggarakan oleh</small>
                                <span class="fw-bold text-truncate d-block"><?= htmlspecialchars($event['organizer_name']) ?></span>
                            </div>
                        </div>
                        
                    </div>
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
                        <p>Platform pembelian tiket event terpercaya. Temukan konser, seminar, dan workshop favoritmu dengan mudah.</p>
                        <div class="social-icons mt-4">
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i></a>
                            <a href="#"><i class="bi bi-facebook"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="mb-3">Bantuan</h5>
                        <ul>
                            <li><a href="#">Cara Pesan</a></li>
                            <li><a href="#">Hubungi Kami</a></li>
                            <li><a href="#">FAQ</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="mb-3">Pembayaran</h5>
                        <div class="p-3 bg-white rounded-3 d-inline-block">
                            <img src="../assets/images/logo_bsi.png" alt="Bank" style="height: 25px;">
                        </div>
                        <div class="mt-3">
                            <small class="text-white-50"><i class="bi bi-shield-check me-2"></i>Transaksi Aman Terenkripsi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <div class="container">
                &copy; <?php echo date('Y'); ?> PT Global Loket Sejahtera.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/navbar.js"></script>
</body>
</html>