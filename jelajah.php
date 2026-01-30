<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']); 

try {
    include_once 'connection/connect.php';
    $pdo = getDatabaseConnection();

    // Ambil nama pengguna jika sudah login
    if ($isLoggedIn) {
        $userId = $_SESSION['user_id'];
        $stmtUser = $pdo->prepare("SELECT name FROM users WHERE user_id = :user_id LIMIT 1");
        $stmtUser->execute(['user_id' => $userId]);
        $user = $stmtUser->fetch();
        $userName = $user ? htmlspecialchars($user['name']) : 'Pengguna';
    } else {
        $userName = null;
    }

    // Get search parameter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_asc';

    // Base query
    $query = "SELECT event_id, title, event_date, location, organizer_name, event_image_path, event_type FROM events"; // Added event_type
    $params = [];
    $conditions = [];

    // Add search condition
    if (!empty($search)) {
        $conditions[] = "title LIKE :search";
        $params[':search'] = "%$search%";
    }
    
    // Add Location Filter
    if (!empty($_GET['location'])) {
        $conditions[] = "location LIKE :location";
        $params[':location'] = "%" . $_GET['location'] . "%";
    }

    // Combine conditions
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Add ORDER BY clause
    switch ($sort) {
        case 'title_asc': $query .= " ORDER BY title ASC"; break;
        case 'title_desc': $query .= " ORDER BY title DESC"; break;
        case 'date_desc': $query .= " ORDER BY event_date DESC"; break;
        default: $query .= " ORDER BY event_date ASC";
    }

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $events = [];
    $userName = 'Pengguna';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelajah Event - BÉLI TIKÉT</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
                <a class="navbar-brand fw-bold fs-3" href="index.php">
                    BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <div class="mx-auto my-3 my-lg-0 w-100 w-lg-50 px-lg-4">
                        <form action="jelajah.php" method="get" class="position-relative">
                            <input type="text" name="search" class="form-control search-bar rounded-pill ps-4" 
                                   placeholder="Cari event..." id="searchInput" 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle" type="submit" style="background: #ff6b6b; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-search text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <a href="index.php" class="nav-link fw-bold">Home</a>
                        
                        <?php if (!$isLoggedIn): ?>
                            <a href="php/register.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm">Daftar</a>
                            <a href="php/login.php" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">Masuk</a>
                        <?php else: ?>
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                                    <div class="profile-icon d-flex justify-content-center align-items-center text-white fw-bold">
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

    <div class="container py-5">
        <div class="row g-4">
            
            <div class="col-lg-3">
                <div class="filter-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0"><i class="bi bi-sliders me-2"></i>Filter</h5>
                        <a href="jelajah.php" class="text-decoration-none small text-muted hover-scale">Reset</a>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="filter-section-title">📍 Lokasi</p>
                        </div>
                        
                        <div class="filter-search mb-3">
                            <input type="text" class="form-control" placeholder="Cari kota..." id="searchLocationInput">
                        </div>

                        <div class="filter-scroll-area" id="locationList">
                            <a href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                               class="filter-list-item <?= !isset($_GET['location']) ? 'selected' : '' ?> text-decoration-none">
                               <span>Semua Lokasi</span>
                               <?php if(!isset($_GET['location'])): ?><i class="bi bi-check-circle-fill"></i><?php endif; ?>
                            </a>
                            
                            <?php
                            $defaultCities = ['Bali', 'Bandung', 'DKI Jakarta', 'Yogyakarta', 'Surabaya', 'Malang', 'Semarang', 'Medan', 'Makassar'];
                            foreach ($defaultCities as $city):
                                $isActive = isset($_GET['location']) && $_GET['location'] === $city;
                            ?>
                            <a href="?location=<?= urlencode($city) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                               class="filter-list-item <?= $isActive ? 'selected' : '' ?> text-decoration-none city-item">
                                <span><?= $city ?></span>
                                <?php if($isActive): ?><i class="bi bi-check-circle-fill"></i><?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div>
                        <p class="filter-section-title mb-3">📅 Waktu</p>
                        <div class="filter-scroll-area">
                             <?php
                            $months = [1=>'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            foreach ($months as $idx => $mName):
                            ?>
                            <div class="filter-list-item">
                                <span><?= $mName ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div class="mb-3 mb-md-0 page-header">
                        <?php if(!empty($search)): ?>
                            <h2 class="mb-1">Hasil: "<?= htmlspecialchars($search) ?>"</h2>
                            <p class="text-muted m-0">Ditemukan <?= count($events) ?> event</p>
                        <?php elseif(!empty($_GET['location'])): ?>
                            <h2 class="mb-1">Event di <?= htmlspecialchars($_GET['location']) ?></h2>
                            <p class="text-muted m-0">Menjelajahi kota favoritmu</p>
                        <?php else: ?>
                            <h2 class="mb-1">Jelajahi Semua Event</h2>
                            <p class="text-muted m-0">Temukan pengalaman seru hari ini</p>
                        <?php endif; ?>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sort dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-sort-down me-2"></i>
                            <?php 
                                $sortLabel = [
                                    'date_asc' => 'Waktu (Terdekat)',
                                    'date_desc' => 'Waktu (Terjauh)',
                                    'title_asc' => 'Nama (A-Z)',
                                    'title_desc' => 'Nama (Z-A)'
                                ];
                                echo $sortLabel[$sort] ?? 'Urutkan';
                            ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 p-2">
                            <li><a class="dropdown-item rounded-3 mb-1" href="?sort=date_asc&search=<?=urlencode($search)?>">Waktu (Terdekat)</a></li>
                            <li><a class="dropdown-item rounded-3 mb-1" href="?sort=date_desc&search=<?=urlencode($search)?>">Waktu (Terjauh)</a></li>
                            <li><a class="dropdown-item rounded-3 mb-1" href="?sort=title_asc&search=<?=urlencode($search)?>">Nama (A-Z)</a></li>
                            <li><a class="dropdown-item rounded-3 mb-1" href="?sort=title_desc&search=<?=urlencode($search)?>">Nama (Z-A)</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row g-4" id="eventGrid">
                    <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event) : ?>
                    <div class="col-md-6 col-xl-4">
                        <a href="php/tiket-page.php?event_id=<?= $event['event_id']; ?>" class="card event-card h-100 text-decoration-none">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($event['event_image_path']) ?>" class="card-img-top event-img" alt="<?= htmlspecialchars($event['title']) ?>">
                                <?php if(isset($event['event_type'])): ?>
                                <span class="category-tag position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm rounded-pill px-3 py-2 fw-bold">
                                    <?= htmlspecialchars($event['event_type']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="mb-2">
                                    <small class="text-danger fw-bold"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($event['event_date'])) ?></small>
                                </div>
                                <h6 class="card-title fw-bold mb-2 line-clamp-2"><?= htmlspecialchars($event['title']) ?></h6>
                                <div class="mt-auto d-flex align-items-center text-muted small">
                                    <i class="bi bi-geo-alt-fill me-1 text-secondary"></i>
                                    <span class="text-truncate"><?= htmlspecialchars($event['location']) ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state text-center">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                            <h4 class="fw-bold mt-3 text-secondary">Oops, Event Tidak Ditemukan</h4>
                            <p class="text-muted">Coba ubah kata kunci pencarian atau filter lokasi.</p>
                            <a href="jelajah.php" class="btn btn-primary rounded-pill px-4 mt-2">Reset Pencarian</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <footer>
        <div class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <h4 class="text-white fw-bold mb-3">BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.</h4>
                        <p style="color: #a0b8d8;">Platform tiket masa kini untuk pengalaman tak terlupakan.</p>
                        <div class="social-icons mt-4">
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i></a>
                            <a href="#"><i class="bi bi-tiktok"></i></a>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container text-center">
                <p class="copyright">&copy; 2024 Beli Tiket (PT Global Loket Sejahtera)</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/navbar.js"></script>
    
    <script>
    document.getElementById('searchLocationInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let items = document.querySelectorAll('.city-item');
        
        items.forEach(function(item) {
            let txtValue = item.textContent || item.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                item.style.display = "flex";
            } else {
                item.style.display = "none";
            }
        });
    });
    </script>
</body>
</html>