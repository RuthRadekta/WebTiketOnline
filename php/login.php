<?php
session_start();

// Periksa apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../connection/connect.php';
    $pdo = getDatabaseConnection();

    try {
        // Siapkan dan eksekusi pernyataan
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->execute();

        // Periksa apakah pengguna ada
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $passwordCorrect = false;

            // Verifikasi password (hash atau plaintext)
            if (password_verify($_POST['password'], $user['password'])) {
                $passwordCorrect = true; // Password cocok dengan hash
            } elseif ($_POST['password'] === $user['password']) {
                $passwordCorrect = true; // Password cocok dengan plaintext

                // Perbarui password plaintext ke hash
                $newHashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
                $updateStmt->bindParam(':password', $newHashedPassword);
                $updateStmt->bindParam(':email', $user['email']);
                $updateStmt->execute();
            }

            // Jika password benar
            if ($passwordCorrect) {
                // Set session
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];

                // Arahkan berdasarkan role
                if ($user['role'] === 'admin') {
                    header("Location: admin-dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                $error = "Kata sandi yang Anda masukkan salah.";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
    } catch (PDOException $e) {
        echo "Koneksi gagal: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BÉLI TIKÉT</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/navbar_footer.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.
                </a>
                
                <div class="d-flex">
                    <a href="../index.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="login-card">
            <div class="text-center">
                <h2 class="login-title">Selamat Datang!</h2>
                <p class="login-subtitle">Silakan masuk untuk melanjutkan petualanganmu.</p>
            </div>

            <?php if (isset($error)) { ?>
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php } ?>

            <form method="POST" action="">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Alamat Email</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Kata Sandi</label>
                </div>

                <button type="submit" class="btn btn-gradient mb-3">
                    Masuk Sekarang <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">Belum punya akun? <a href="register.php" class="text-link">Daftar Gratis</a></p>
                <a href="#" class="text-muted small text-decoration-none">Lupa kata sandi?</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <h4 class="mb-2 text-white">BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.</h4>
                        <p class="small text-white-50 mb-0">Platform tiket event masa depan.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="social-icons">
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i></a>
                            <a href="#"><i class="bi bi-facebook"></i></a>
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
</body>
</html>