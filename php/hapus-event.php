<?php
// Mulai sesi
session_start();

// Header agar browser tahu ini respon JSON
header('Content-Type: application/json');

// Periksa apakah pengguna memiliki peran admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "error" => "Akses ditolak."]);
    exit();
}

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loket_com";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Koneksi database gagal."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id']) && is_numeric($_POST['event_id'])) {
    $event_id = (int) $_POST['event_id'];

    // 1. AMBIL DATA GAMBAR DULU SEBELUM DIHAPUS
    // Kita perlu path gambar untuk menghapus filenya dari folder
    $sql_get_image = "SELECT event_image_path FROM events WHERE event_id = ?";
    $stmt_img = $conn->prepare($sql_get_image);
    $stmt_img->bind_param("i", $event_id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    
    $image_path_to_delete = null;
    if ($row = $result_img->fetch_assoc()) {
        $image_path_to_delete = $row['event_image_path'];
    }
    $stmt_img->close();

    // 2. HAPUS DATA DARI DATABASE
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? LIMIT 1");
    $stmt->bind_param("i", $event_id);

    try {
        if ($stmt->execute()) {
            // Cek apakah ada baris yang terhapus
            if ($stmt->affected_rows > 0) {
                
                // 3. JIKA SUKSES DI DB, HAPUS FILE GAMBAR FISIK (Opsional tapi disarankan)
                // Path di DB: img/konser/nama.jpg
                // Path fisik script ini: php/hapus-event.php
                // Maka perlu mundur satu folder: ../img/konser/nama.jpg
                if ($image_path_to_delete && file_exists("../" . $image_path_to_delete)) {
                    unlink("../" . $image_path_to_delete);
                }

                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Event tidak ditemukan atau sudah dihapus."]);
            }
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        // Menangkap error jika gagal hapus (misal karena ada relasi Foreign Key di tabel tiket/transaksi)
        // Jika event sudah pernah dibeli tiketnya, biasanya database menolak penghapusan (Constraint Fails)
        echo json_encode(["success" => false, "error" => "Gagal menghapus. Event ini mungkin memiliki data tiket atau transaksi terkait."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Permintaan tidak valid."]);
}

$conn->close();
?>