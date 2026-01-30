<?php
session_start();

// Periksa Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../connection/connect.php';
$pdo = getDatabaseConnection();
$current_page = basename($_SERVER['PHP_SELF']);

// Ambil User
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = !empty($user['name']) ? htmlspecialchars($user['name']) : 'Pengguna';

// Ambil Riwayat Tiket
try {
    $stmt = $pdo->prepare("
        SELECT 
            MAX(ph.purchase_id) as purchase_id, 
            ph.purchase_date, 
            SUM(ph.quantity) AS total_quantity, 
            SUM(ph.total_price) AS total_price, 
            GROUP_CONCAT(CONCAT(ph.quantity, 'x ', t.ticket_type) SEPARATOR ', ') AS ticket_details, 
            e.title AS event_title, 
            e.event_date, 
            e.event_image_path, 
            o.payment_status 
        FROM purchase_history ph
        JOIN events e ON ph.event_id = e.event_id
        JOIN tickets t ON ph.ticket_id = t.ticket_id
        JOIN orders o ON ph.order_id = o.order_id
        WHERE ph.user_id = :user_id 
        GROUP BY ph.event_id, ph.purchase_date, e.title, e.event_date, e.event_image_path, o.payment_status
        ORDER BY ph.purchase_date DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $purchases = []; // Fail safe
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - BÉLI TIKÉT</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link href="../css/profile.css" rel="stylesheet">
</head>

<body>

    <div class="sidebar" id="sidebar">
        <a href="../index.php" class="logo">
            <i class="bi bi-ticket-perforated-fill"></i>
            <span>BÉLI TIKÉT</span>
        </a>

        <div class="sidebar-menu">
            <a href="../index.php" class="nav-link">
                <i class="bi bi-house-door"></i><span>Beranda</span>
            </a>
            <a href="../jelajah.php" class="nav-link">
                <i class="bi bi-compass"></i><span>Jelajah Event</span>
            </a>
            <div class="mt-3 mb-2 text-white-50 small px-3 text-uppercase fw-bold" style="font-size: 0.75rem;">Akun</div>
            <a href="profile.php" class="nav-link">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
            <a href="riwayat.php" class="nav-link active"> <i class="bi bi-ticket-detailed"></i><span>Tiket Saya</span>
            </a>
            <a href="pengaturan.php" class="nav-link">
                <i class="bi bi-gear"></i><span>Pengaturan</span>
            </a>
        </div>

        <div class="toggle-btn-container">
            <button class="toggle-button" onclick="toggleSidebar()">
                <i class="bi bi-layout-sidebar"></i><span>Minimize</span>
            </button>
        </div>
    </div>

    <div class="content-container" id="content">
        
        <div class="profile-top-header">
            <div>
                <h2 class="mb-0">Tiket Saya</h2>
                <p class="text-muted mb-0">Kelola dan lihat riwayat pembelian tiket event Anda.</p>
            </div>
            
            <div class="dropdown">
                <div class="user-dropdown" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=random" alt="Avatar">
                    <span class="fw-bold d-none d-md-block text-dark"><?php echo $userName; ?></span>
                    <i class="bi bi-chevron-down ms-2 small text-muted"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2" style="border-radius: 12px;">
                    <li><a class="dropdown-item" href="../index.php">Ke Beranda</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>

        <ul class="nav nav-tabs custom-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-events" type="button" role="tab">Event Aktif</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past-events" type="button" role="tab">Riwayat Belanja</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            
            <div class="tab-pane fade show active" id="active-events" role="tabpanel">
                <?php 
                $hasActive = false;
                foreach ($purchases as $p) {
                    if (strtotime($p['event_date']) > time()) {
                        $hasActive = true;
                        renderTicketCard($p);
                    }
                }
                if (!$hasActive) {
                    echo '<div class="empty-state"><i class="bi bi-ticket-perforated"></i><p>Belum ada tiket aktif.</p><a href="../jelajah.php" class="btn btn-sm btn-primary rounded-pill px-4">Cari Event</a></div>';
                }
                ?>
            </div>

            <div class="tab-pane fade" id="past-events" role="tabpanel">
                <?php 
                $hasPast = false;
                foreach ($purchases as $p) {
                    if (strtotime($p['event_date']) <= time()) {
                        $hasPast = true;
                        renderTicketCard($p);
                    }
                }
                if (!$hasPast) {
                    echo '<div class="empty-state"><i class="bi bi-calendar-check"></i><p>Belum ada riwayat event yang selesai.</p></div>';
                }
                ?>
            </div>

        </div>
    </div>

    <?php
    function renderTicketCard($purchase) {
        $statusClass = 'status-pending';
        $statusText = 'Menunggu';
        
        if ($purchase['payment_status'] === 'completed') {
            $statusClass = 'status-success';
            $statusText = 'Lunas';
        } elseif ($purchase['payment_status'] === 'failed') {
            $statusClass = 'status-failed';
            $statusText = 'Gagal';
        }

        $imagePath = '../' . $purchase['event_image_path'];
    ?>
    <div class="ticket-card position-relative">
        <div class="row g-0">
            <div class="col-md-4 position-relative">
                <span class="ticket-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="ticket-img" alt="Event">
            </div>
            <div class="col-md-8">
                <div class="ticket-body h-100 d-flex flex-column justify-content-center">
                    <div class="ticket-date text-uppercase">
                        <i class="bi bi-calendar-event me-1"></i> 
                        <?php echo date('d F Y, H:i', strtotime($purchase['event_date'])); ?>
                    </div>
                    
                    <h5 class="ticket-title"><?php echo htmlspecialchars($purchase['event_title']); ?></h5>
                    
                    <div class="ticket-info-row mt-2">
                        <i class="bi bi-ticket-detailed"></i>
                        <span><?php echo htmlspecialchars($purchase['ticket_details']); ?></span>
                    </div>
                    
                    <div class="ticket-info-row">
                        <i class="bi bi-geo-alt"></i>
                        <span>Lokasi Event (Lihat Detail)</span>
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Total Pembayaran</small>
                            <span class="ticket-price">Rp <?php echo number_format($purchase['total_price'], 0, ',', '.'); ?></span>
                        </div>
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3">Lihat E-Ticket</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('shrink');
            document.getElementById('content').classList.toggle('shrink');
        }
    </script>
</body>
</html>