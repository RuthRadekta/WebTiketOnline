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

// Ambil User Data (Untuk Sidebar & Navbar)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userName = !empty($user['name']) ? htmlspecialchars($user['name']) : 'Pengguna';

// Proses Hapus Akun
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $deleteStmt->bindParam(':user_id', $user_id);
        $deleteStmt->execute();        
        
        session_destroy();
        echo "<script>
            alert('Akun berhasil dihapus. Sampai jumpa!');
            window.location.href = '../index.php';
        </script>";
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus akun: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - BÉLI TIKÉT</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
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
            <a href="riwayat.php" class="nav-link">
                <i class="bi bi-ticket-detailed"></i><span>Tiket Saya</span>
            </a>
            <a href="pengaturan.php" class="nav-link active"> <i class="bi bi-gear"></i><span>Pengaturan</span>
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
                <h2 class="mb-0">Pengaturan</h2>
                <p class="text-muted mb-0">Kelola preferensi dan keamanan akun Anda.</p>
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

        <div class="profile-card">
            
            <h5 class="fw-bold mb-3">Preferensi Umum</h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="notifSwitch" checked>
                <label class="form-check-label" for="notifSwitch">Terima notifikasi email tentang event baru</label>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" id="promoSwitch">
                <label class="form-check-label" for="promoSwitch">Terima info promo & diskon</label>
            </div>

            <hr class="my-4 opacity-25">

            <div class="danger-zone">
                <div class="danger-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Zona Bahaya
                </div>
                <p class="danger-desc">Tindakan di bawah ini tidak dapat dibatalkan. Harap berhati-hati.</p>

                <div class="accordion" id="accordionDelete">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDelete">
                                <i class="bi bi-trash3 me-2"></i> Tutup Akun Saya
                            </button>
                        </h2>
                        <div id="collapseDelete" class="accordion-collapse collapse" data-bs-parent="#accordionDelete">
                            <div class="accordion-body">
                                <h6 class="fw-bold text-dark">Apakah Anda yakin?</h6>
                                <p class="small text-muted mb-3">Dengan menutup akun, Anda akan kehilangan akses ke:</p>
                                <ul class="warning-list mb-4">
                                    <li>Seluruh riwayat tiket dan transaksi.</li>
                                    <li>Data profil dan preferensi yang tersimpan.</li>
                                    <li>Poin atau reward yang mungkin Anda miliki.</li>
                                </ul>

                                <form method="POST">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="agreeCloseAccount" onchange="toggleDeleteBtn()">
                                        <label class="form-check-label small text-dark" for="agreeCloseAccount">
                                            Saya mengerti dan ingin melanjutkan penghapusan akun permanen.
                                        </label>
                                    </div>
                                    <button type="submit" id="btnDelete" name="delete_account" class="btn btn-danger-soft w-100" disabled>
                                        Konfirmasi Tutup Akun
                                    </button>
                                </form>

                                <?php if (!empty($error_message)) : ?>
                                    <div class="alert alert-danger mt-3 py-2 small">
                                        <i class="bi bi-exclamation-circle me-1"></i> <?php echo $error_message; ?> 
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('shrink');
            document.getElementById('content').classList.toggle('shrink');
        }

        // Enable Delete Button Logic
        function toggleDeleteBtn() {
            const checkbox = document.getElementById('agreeCloseAccount');
            const btn = document.getElementById('btnDelete');
            btn.disabled = !checkbox.checked;
        }
    </script>
</body>
</html>