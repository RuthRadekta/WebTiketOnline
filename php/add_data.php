<?php
session_start();

// Cek Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Koneksi Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loket_com";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = $conn->real_escape_string($_POST['location']);
    $organizer_name = $conn->real_escape_string($_POST['organizer_name']);
    $event_type = $conn->real_escape_string($_POST['event_type']);
    $dress_code = $conn->real_escape_string($_POST['dress_code']);
    $min_age = (int)$_POST['min_age'];
    $facilities = $conn->real_escape_string($_POST['facilities']);
    
    // --- LOGIKA UPLOAD GAMBAR BARU ---
    $event_image_path = ""; // Default kosong jika gagal/tidak ada gambar
    
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        // 1. Tentukan folder fisik penyimpanan (Relatif terhadap file ini: php/add_data.php)
        // Kita mundur satu folder (../) lalu masuk ke img/konser/
        $target_dir = "../img/konser/";
        
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Ambil ekstensi file
        $file_extension = pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION);
        
        // Buat nama unik agar tidak menimpa file lain (Timestamp + Random ID)
        $new_filename = time() . "_" . uniqid() . "." . $file_extension;
        
        // Path fisik lengkap untuk fungsi move_uploaded_file
        $target_file_physical = $target_dir . $new_filename;
        
        // Path "bersih" untuk disimpan di database (agar bisa dibaca dari root/index.php)
        // Hasilnya jadi: img/konser/namafile.jpg
        $path_for_db = "img/konser/" . $new_filename;
        
        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            // Upload file ke folder fisik
            if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file_physical)) {
                // Jika sukses upload, set variabel untuk database
                $event_image_path = $path_for_db;
            } else {
                $_SESSION['message'] = "Gagal mengupload gambar ke folder tujuan.";
                $_SESSION['msg_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Format file tidak didukung (hanya JPG, JPEG, PNG, WEBP).";
            $_SESSION['msg_type'] = "warning";
        }
    }
    // --- AKHIR LOGIKA UPLOAD ---

    // Insert Query (Pastikan kolom updated_at sudah ada di database sesuai perbaikan sebelumnya)
    $sql = "INSERT INTO events (title, description, event_date, location, organizer_name, event_type, dress_code, min_age, facilities, event_image_path, created_at, updated_at) 
            VALUES ('$title', '$description', '$event_date', '$location', '$organizer_name', '$event_type', '$dress_code', '$min_age', '$facilities', '$event_image_path', NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Event berhasil ditambahkan!";
        $_SESSION['msg_type'] = "success";
        header("Location: admin-dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Error Database: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Event Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Tambah Event Baru</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['message']; 
                                unset($_SESSION['message']); 
                                unset($_SESSION['msg_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Event</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Penyelenggara</label>
                                <input type="text" name="organizer_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal & Waktu</label>
                                <input type="datetime-local" name="event_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Event</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipe Event</label>
                                <select name="event_type" class="form-select">
                                    <option value="Concert">Konser</option>
                                    <option value="Seminar">Seminar</option>
                                    <option value="Workshop">Workshop</option>
                                    <option value="Sports">Olahraga</option>
                                    <option value="Other">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Dress Code</label>
                                <input type="text" name="dress_code" class="form-control" placeholder="Cth: Casual">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Minimal Usia</label>
                                <input type="number" name="min_age" class="form-control" min="0" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fasilitas</label>
                            <textarea name="facilities" class="form-control" rows="2" placeholder="Cth: WiFi, Parkir"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Gambar/Poster Event</label>
                            <input type="file" name="event_image" class="form-control" accept="image/*" required>
                            <div class="form-text text-muted">
                                File akan disimpan di folder: <strong>img/konser/</strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="admin-dashboard.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan Event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>