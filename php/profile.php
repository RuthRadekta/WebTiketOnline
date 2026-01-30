<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$current_page = basename($_SERVER['PHP_SELF']);

// Ambil data user dari DB untuk ditampilkan di Navbar/Form
include_once '../connection/connect.php';
try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE user_id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
    $userName = $user ? htmlspecialchars($user['name']) : 'Pengguna';
} catch(Exception $e) {
    $userName = 'Pengguna';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - BÉLI TIKÉT</title>
    
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
            <a href="profile.php" class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
            <a href="riwayat.php" class="nav-link">
                <i class="bi bi-ticket-detailed"></i><span>Tiket Saya</span>
            </a>
            <a href="pengaturan.php" class="nav-link <?php echo $current_page == 'pengaturan.php' ? 'active' : ''; ?>">
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
                <h2 class="mb-0">Profil Kamu</h2>
                <p class="text-muted mb-0">Kelola informasi profil Anda untuk mengontrol, melindungi dan mengamankan akun.</p>
            </div>
            
            <div class="dropdown">
                <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
            <h4 class="section-title">Informasi Dasar</h4>
            
            <form id="profile-form">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" readonly title="Email tidak dapat diubah">
                            <div class="form-text text-muted small"><i class="bi bi-lock-fill"></i> Email tidak dapat diubah.</div>
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="" disabled selected>Pilih...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="08xxxxxxxx">
                        </div>
                        <div class="mb-3">
                            <label for="ktp_number" class="form-label">Nomor KTP (NIK)</label>
                            <input type="text" class="form-control" id="ktp_number" name="ktp_number" placeholder="16 Digit Angka">
                        </div>
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-save" id="btn-save">
                        <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle Logic
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            
            sidebar.classList.toggle('shrink');
            content.classList.toggle('shrink');
        }

        // Fetch Data Logic
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch('profile-api.php');
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('name').value = data.name || '';
                    document.getElementById('email').value = data.email || '';
                    document.getElementById('phone_number').value = data.phone_number || '';
                    document.getElementById('ktp_number').value = data.ktp_number || '';
                    document.getElementById('date_of_birth').value = data.date_of_birth || '';
                    document.getElementById('gender').value = data.gender || '';
                }
            } catch (error) {
                console.error("Gagal mengambil data profil", error);
            }
        });

        // Submit Form Logic
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-save');
            const originalText = btn.innerHTML;
            
            // Loading State
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            btn.disabled = true;

            const formData = new FormData(e.target);
            const body = {
                csrf_token: formData.get('csrf_token'),
                name: formData.get('name'),
                phone_number: formData.get('phone_number'),
                ktp_number: formData.get('ktp_number'),
                date_of_birth: formData.get('date_of_birth'),
                gender: formData.get('gender'),
            };

            try {
                const response = await fetch('profile-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });

                if (response.ok) {
                    alert('Data berhasil diperbarui!');
                } else {
                    alert('Gagal memperbarui data.');
                }
            } catch (error) {
                alert('Terjadi kesalahan koneksi.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>