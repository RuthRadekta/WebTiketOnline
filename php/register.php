<?php
session_start();

// Periksa apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../connection/connect.php';
    $pdo = getDatabaseConnection();

    // Ambil data dari form
    $email = $_POST['email'];
    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if ($password_input !== $confirm_password) {
        $error = "Kata sandi dan konfirmasi kata sandi tidak cocok.";
    } else {
        try {
            // Periksa apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Email sudah terdaftar. Silakan gunakan email lain.";
            } else {
                // Hash password
                $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

                // Siapkan dan eksekusi pernyataan untuk memasukkan data baru
                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();

                // Redirect ke halaman login setelah berhasil registrasi
                header("Location: login.php?register=success");
                exit();
            }
        } catch(PDOException $e) {
            echo "Koneksi gagal: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BÉLI TIKÉT</title>
    
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
                
                <div class="d-flex gap-2">
                    <a href="../index.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <div class="main-content">
        <div class="login-card">
            <div class="text-center">
                <h2 class="login-title">Buat Akun Baru</h2>
                <p class="login-subtitle">Bergabunglah untuk akses tiket eksklusif.</p>
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

                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Kata Sandi</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <label for="confirm_password">Ulangi Kata Sandi</label>
                </div>

                <button type="submit" class="btn btn-gradient mb-3">
                    Daftar Sekarang <i class="bi bi-person-plus-fill ms-2"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">Sudah punya akun? <a href="login.php" class="text-link">Masuk di sini</a></p>
                <div class="mt-3">
                    <small class="text-muted" style="font-size: 0.75rem;">Dengan mendaftar, Anda menyetujui <a href="#" class="text-decoration-none">Syarat & Ketentuan</a> kami.</small>
                </div>
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