<?php
// Mulai sesi
session_start();

// Periksa apakah pengguna telah login dan memiliki peran admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php'); // Redirect ke halaman utama jika bukan admin
    exit();
}

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "loket_com";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// --- LOGIKA GRAFIK (TIDAK BERUBAH) ---
$topTicketsData = [];
$sql = "
    SELECT 
        events.title AS event_title,
        tickets.ticket_type AS ticket_type,
        SUM(purchase_history.quantity) AS total_quantity
    FROM 
        purchase_history
    INNER JOIN 
        events ON purchase_history.event_id = events.event_id
    INNER JOIN 
        tickets ON purchase_history.ticket_id = tickets.ticket_id
    GROUP BY 
        events.title, tickets.ticket_type
    ORDER BY 
        total_quantity DESC
    LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topTicketsData[] = [
            'label' => $row['event_title'] . ' - ' . $row['ticket_type'],
            'quantity' => $row['total_quantity']
        ];
    }
}

$ticketLabels = array_column($topTicketsData, 'label');
$ticketQuantities = array_column($topTicketsData, 'quantity');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/admin-dashboard.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar_footer.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="../index.php">BÉLI TIKÉT</a>
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <a href="#" class="btn btn-light d-flex align-items-center gap-2 px-3" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-circle-user fa-lg"></i>
                            <span>Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li class="dropdown-header">Halo, Admin</li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container my-4">
        <h1 class="mb-4">Selamat Datang, Admin</h1>

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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Data Event</h4>
            <a href="add_data.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Event Baru
            </a>
        </div>

        <?php
        // Pagination logic
        $limit = 5; 
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
        $offset = ($currentPage - 1) * $limit; 

        try {
            $sqlCount = "SELECT COUNT(*) as total FROM events";
            $resultCount = $conn->query($sqlCount);
            $totalData = $resultCount->fetch_assoc()['total'];
            $totalPages = ceil($totalData / $limit); 

            // Order by ID Descending agar data baru muncul paling atas
            $sql = "SELECT * FROM events ORDER BY event_id DESC LIMIT $limit OFFSET $offset";
            $result = $conn->query($sql);
        ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Organizer</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['event_id'] . "</td>";
                            echo "<td>" . $row['title'] . "</td>";
                            echo "<td>" . date('d M Y H:i', strtotime($row['event_date'])) . "</td>";
                            echo "<td>" . $row['location'] . "</td>";
                            echo "<td>" . $row['organizer_name'] . "</td>";
                            echo "<td>";
                            echo "<a href='edit-event.php?event_id=" . $row['event_id'] . "' class='btn btn-warning btn-sm me-1'><i class='fas fa-edit'></i></a>";
                            echo "<button class='btn btn-danger btn-sm' onclick='hapusEvent(" . $row['event_id'] . ")'><i class='fas fa-trash'></i></button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Tidak ada data event.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center custom-pagination">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">&laquo; Previous</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Next &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <?php
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Kesalahan: " . $e->getMessage() . "</div>";
        }
        ?>

        <div class="mb-4 mt-5">
            <h4>Tiket Terlaris</h4>
            <div class="card p-3 shadow-sm">
                <canvas id="topTicketsChart" style="width: 100%; height: 400px;"></canvas>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Beli Tiket. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        const ticketLabels = <?php echo json_encode($ticketLabels); ?>;
        const ticketData = <?php echo json_encode($ticketQuantities); ?>;

        const ctxTopTickets = document.getElementById('topTicketsChart').getContext('2d');
        const topTicketsChart = new Chart(ctxTopTickets, {
            type: 'bar',
            data: {
                labels: ticketLabels,
                datasets: [{
                    label: 'Tiket Terjual',
                    data: ticketData,
                    backgroundColor: '#0d6efd',
                    borderColor: '#0b5ed7',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        function hapusEvent(id) {
            if(confirm('Yakin ingin menghapus event ini? Data yang dihapus tidak bisa dikembalikan.')) {
                // Buat data form untuk dikirim via POST
                let formData = new FormData();
                formData.append('event_id', id);

                // Kirim request ke hapus-event.php
                fetch('hapus-event.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Ubah respon jadi JSON
                .then(data => {
                    if (data.success) {
                        alert('Event berhasil dihapus!');
                        location.reload(); // Refresh halaman otomatis
                    } else {
                        alert('Gagal menghapus: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan koneksi atau server.');
                });
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>