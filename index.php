<?php
session_start(); 
$isLoggedIn = isset($_SESSION['user_id']); 

// Include the database connection file
include_once 'connection/connect.php';
$pdo = getDatabaseConnection();

// Query List Event (Terbaru)
$query = "SELECT event_id, title, event_date, location, organizer_name, event_image_path, event_type 
          FROM events 
          ORDER BY event_date ASC LIMIT 8";
$stmt = $pdo->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll();

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

// Top Events (Random)
$stmtTopEvents = $pdo->prepare("SELECT event_id, title, event_image_path 
                                FROM events 
                                WHERE event_image_path IS NOT NULL 
                                ORDER BY RAND() LIMIT 3");
$stmtTopEvents->execute();
$topEvents = $stmtTopEvents->fetchAll();

// Carousel (Featured)
$stmtCarousel = $pdo->prepare("SELECT event_id, title, event_image_path, event_date FROM events WHERE event_image_path IS NOT NULL ORDER BY event_date DESC LIMIT 3");
$stmtCarousel->execute();
$carouselImages = $stmtCarousel->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BÉLI TIKÉT - Jelajahi Event Seru</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="css/navbar_footer.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
</head>

<body>
    
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <div class="mx-auto my-3 my-lg-0 w-100 w-lg-50 px-lg-4">
                        <form action="jelajah.php" method="get" class="position-relative">
                            <input type="text" name="search" class="form-control search-bar" 
                                   placeholder="Cari event impianmu..." id="searchInput">
                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle" type="submit" style="background: #ff6b6b; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class="bi bi-search text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                    </div>

                    <div class="d-flex align-items-center gap-3 justify-content-end">
                        <a href="jelajah.php" class="nav-link">Jelajah</a>

                        <?php if (!$isLoggedIn): ?>
                            <a href="php/register.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">Daftar</a>
                            <a href="php/login.php" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold border-0">Masuk</a>
                        <?php else: ?>
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                                    <div class="profile-icon">
                                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                                    <li><h6 class="dropdown-header">Halo, <?php echo htmlspecialchars($userName); ?></h6></li>
                                    <li><a class="dropdown-item" href="php/riwayat.php">Tiket Saya</a></li>
                                    <li><a class="dropdown-item" href="php/profile.php">Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="php/logout.php">Keluar</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="container-fluid px-0 mb-5">
            <div id="headerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                <div class="carousel-indicators creative-indicators">
                    <?php foreach ($carouselImages as $index => $image): ?>
                        <button type="button" data-bs-target="#headerCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div class="carousel-inner">
                    <?php foreach ($carouselImages as $index => $image): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="hero-overlay"></div>
                        <img src="<?php echo htmlspecialchars($image['event_image_path']); ?>" class="d-block w-100 hero-img" alt="Event Banner">
                        
                        <div class="carousel-caption d-none d-md-block text-start pb-5 mb-4">
                            <div class="container">
                                <span class="badge bg-danger rounded-pill mb-3 px-3 py-2 shadow-sm">Featured Event</span>
                                <h1 class="display-3 fw-bold mb-2"><?php echo htmlspecialchars($image['title']); ?></h1>
                                <p class="lead mb-4 text-white-50">
                                    <i class="bi bi-calendar-event me-2"></i><?php echo date('d F Y', strtotime($image['event_date'])); ?>
                                </p>
                                <a href="php/tiket-page.php?event_id=<?php echo $image['event_id']; ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg">
                                    Beli Tiket
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark">Event Pilihan</h3>
                <a href="jelajah.php" class="text-decoration-none fw-bold" style="color: var(--primary-color);">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-3">
                    <a href="php/tiket-page.php?event_id=<?php echo $event['event_id']; ?>" class="card event-card h-100 text-decoration-none">
                        <div class="position-relative overflow-hidden">
                            <img src="<?php echo htmlspecialchars($event['event_image_path']); ?>" class="card-img-top event-img" alt="Event Image">
                            <?php if(isset($event['event_type'])): ?>
                            <span class="category-tag position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm rounded-pill px-3 py-2 fw-bold">
                                <?php echo htmlspecialchars($event['event_type']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="mb-2">
                                <small class="text-danger fw-bold"><i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y', strtotime($event['event_date'])); ?></small>
                            </div>
                            <h6 class="card-title mb-2 line-clamp-2"><?php echo htmlspecialchars($event['title']); ?></h6>
                            <div class="mt-auto d-flex align-items-center text-muted small">
                                <i class="bi bi-geo-alt-fill me-1 text-secondary"></i>
                                <span class="text-truncate"><?php echo htmlspecialchars($event['location']); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-4 border border-dashed">
                        <i class="bi bi-calendar-x text-muted mb-3" style="font-size: 3rem;"></i>
                        <p class="text-muted fs-5">Belum ada event yang tersedia saat ini.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="mt-5 pt-4">
                <div class="p-4 p-md-5 rounded-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0b2341 0%, #1a3c66 100%);">
                    <div class="position-relative z-1">
                        <h3 class="fw-bold text-white text-center mb-5">🔥 Paling Banyak Dicari</h3>
                        
                        <div class="row justify-content-center g-4">
                            <?php foreach ($topEvents as $index => $event): ?>
                            <div class="col-md-4">
                                <a href="php/tiket-page.php?event_id=<?php echo $event['event_id']; ?>" class="d-block text-decoration-none hover-lift">
                                    <div class="position-relative">
                                        <div class="rank-badge-big text-white opacity-25" style="position:absolute; top:-20px; left:10px; font-size:4rem; font-weight:900; z-index:2;">
                                            <?php echo $index + 1; ?>
                                        </div>
                                        <img src="<?php echo htmlspecialchars($event['event_image_path']); ?>" 
                                             alt="Top Event" 
                                             class="rounded-4 shadow-lg w-100 object-fit-cover" 
                                             style="height: 250px; border: 4px solid rgba(255,255,255,0.1);">
                                    </div>
                                    <div class="mt-3 text-center">
                                        <h6 class="text-white text-truncate px-3"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    </div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6">
                        <h4 class="mb-3">BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.</h4>
                        <p>Platform pembelian tiket event terpercaya. Temukan konser, seminar, dan workshop favoritmu dengan mudah dan aman.</p>
                        <div class="social-icons mt-4">
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i></a>
                            <a href="#"><i class="bi bi-facebook"></i></a>
                            <a href="#"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="mb-3">Tentang</h5>
                        <ul>
                            <li><a href="php/tentang_kami.php">Tentang Kami</a></li>
                            <li><a href="#">Hubungi Kami</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Karir</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="mb-3">Dukungan</h5>
                        <ul>
                            <li><a href="#">Pusat Bantuan</a></li>
                            <li><a href="#">Syarat & Ketentuan</a></li>
                            <li><a href="#">Kebijakan Privasi</a></li>
                            <li><a href="#">Panduan</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="mb-3">Pembayaran</h5>
                        <div class="p-3 bg-white rounded-3 d-inline-block">
                            <img src="assets/images/logo_bsi.png" alt="Bank Partner" style="height: 30px; width: auto;">
                        </div>
                        <div class="mt-3">
                            <small class="text-white-50"><i class="fas fa-lock text-success me-2"></i>Transaksi aman terenkripsi SSL</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <div class="container">
                &copy; <?php echo date('Y'); ?> PT Global Loket Sejahtera. Made with <i class="bi bi-heart-fill text-danger mx-1"></i> in Indonesia.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/navbar.js"></script>
</body>
</html>