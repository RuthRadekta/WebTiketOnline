<?php
// Mulai sesi
session_start();

// Periksa apakah pengguna telah login dan memiliki peran admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); // Redirect ke halaman utama jika bukan admin
    exit();
}

// --- KONEKSI DATABASE (MENGGUNAKAN PDO) ---
include_once '../connection/connect.php'; 

try {
    $pdo = getDatabaseConnection(); // Menggunakan fungsi yang sudah ada di connect.php
} catch (Exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// --- LOGIKA DATA STATISTIK ---
// 1. Total Event
$stmt = $pdo->query("SELECT COUNT(*) FROM events");
$totalEvents = $stmt->fetchColumn();

// 2. Total Tiket Terjual
$stmt = $pdo->query("SELECT SUM(quantity) FROM purchase_history");
$totalSold = $stmt->fetchColumn() ?: 0; // Jika null (belum ada penjualan), set 0

// 3. Total Pendapatan
$stmt = $pdo->query("SELECT SUM(total_price) FROM purchase_history");
$totalRevenue = $stmt->fetchColumn() ?: 0;


// --- LOGIKA GRAFIK (Top 5 Tiket) ---
$topTicketsData = [];
$sql = "SELECT events.title AS event_title, tickets.ticket_type AS ticket_type, SUM(purchase_history.quantity) AS total_quantity
        FROM purchase_history
        INNER JOIN events ON purchase_history.event_id = events.event_id
        INNER JOIN tickets ON purchase_history.ticket_id = tickets.ticket_id
        GROUP BY events.title, tickets.ticket_type
        ORDER BY total_quantity DESC LIMIT 5";

$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $topTicketsData[] = [
        'label' => substr($row['event_title'], 0, 15) . '... (' . $row['ticket_type'] . ')',
        'quantity' => $row['total_quantity']
    ];
}

$ticketLabels = array_column($topTicketsData, 'label');
$ticketQuantities = array_column($topTicketsData, 'quantity');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BÉLI TIKÉT</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" type="text/css" href="../css/navbar_footer.css">
    <link rel="stylesheet" type="text/css" href="../css/admin-dashboard.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold" href="../index.php">
                    BÉLI<span style="color: #ff6b6b;">TIKÉT</span> <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">ADMIN PANEL</span>
                </a>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-dark" id="dropdownMenuButton" data-bs-toggle="dropdown">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                                <i class="fas fa-user-shield text-primary"></i>
                            </div>
                            <span class="fw-bold d-none d-md-block">Administrator</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2" aria-labelledby="dropdownMenuButton" style="border-radius: 12px;">
                            <li><a class="dropdown-item rounded" href="../index.php">Lihat Website</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger rounded" href="logout.php">Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container-fluid px-4 my-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-0">Dashboard Overview</h2>
                <p class="text-muted small">Ringkasan aktivitas penjualan tiket Anda.</p>
            </div>
            <a href="add_data.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i> Tambah Event
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Event Aktif</div>
                        <div class="stat-value"><?php echo $totalEvents; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-green">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div>
                        <div class="stat-label">Tiket Terjual</div>
                        <div class="stat-value"><?php echo number_format($totalSold); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-purple">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-value">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['msg_type']; ?> alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); 
                    unset($_SESSION['msg_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title-custom m-0">Daftar Event Terbaru</h5>
                    </div>

                    <?php
                    // Pagination logic
                    $limit = 5; 
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
                    $offset = ($currentPage - 1) * $limit; 

                    try {
                        // Hitung total data
                        $stmtCount = $pdo->query("SELECT COUNT(*) FROM events");
                        $totalData = $stmtCount->fetchColumn();
                        $totalPages = ceil($totalData / $limit); 

                        // Ambil data event dengan PDO
                        $stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_id DESC LIMIT :limit OFFSET :offset");
                        // PDO Limit & Offset harus di-bind sebagai Integer
                        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();
                        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="30%">Nama Event</th>
                                    <th>Tanggal</th>
                                    <th>Lokasi</th>
                                    <th>Organizer</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (count($events) > 0) {
                                    foreach ($events as $row) {
                                        echo "<tr>";
                                        echo "<td class='text-muted'>#" . $row['event_id'] . "</td>";
                                        echo "<td><span class='fw-bold text-dark'>" . htmlspecialchars($row['title']) . "</span></td>";
                                        echo "<td class='text-muted small'><i class='far fa-clock me-1'></i>" . date('d M Y', strtotime($row['event_date'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                                        echo "<td><span class='badge bg-light text-dark border'>" . htmlspecialchars($row['organizer_name']) . "</span></td>";
                                        echo "<td class='text-end'>";
                                        echo "<a href='edit-event.php?event_id=" . $row['event_id'] . "' class='btn-action btn-edit me-2' title='Edit'><i class='fas fa-pen'></i></a>";
                                        echo "<button class='btn-action btn-delete' onclick='hapusEvent(" . $row['event_id'] . ")' title='Hapus'><i class='fas fa-trash'></i></button>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Belum ada data event.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage - 1; ?>"><i class="fas fa-chevron-left"></i></a></li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $currentPage + 1; ?>"><i class="fas fa-chevron-right"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <?php
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>Kesalahan: " . $e->getMessage() . "</div>";
                    }
                    ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-card h-100">
                    <h5 class="card-title-custom">Statistik Penjualan</h5>
                    <p class="text-muted small mb-4">5 Event dengan penjualan tiket tertinggi.</p>
                    <div style="position: relative; height: 300px;">
                        <canvas id="topTicketsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Data Grafik dari PHP
        const ticketLabels = <?php echo json_encode($ticketLabels); ?>;
        const ticketData = <?php echo json_encode($ticketQuantities); ?>;

        // Konfigurasi Chart.js
        const ctxTopTickets = document.getElementById('topTicketsChart').getContext('2d');
        
        // Buat Gradient
        const gradient = ctxTopTickets.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#4318FF');
        gradient.addColorStop(1, 'rgba(67, 24, 255, 0.2)');

        new Chart(ctxTopTickets, {
            type: 'bar',
            data: {
                labels: ticketLabels,
                datasets: [{
                    label: 'Tiket Terjual',
                    data: ticketData,
                    backgroundColor: gradient,
                    borderRadius: 8,
                    borderSkipped: false,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1B2559',
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#E9EDF7' },
                        ticks: { font: { family: "'Quicksand', sans-serif" } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Quicksand', sans-serif", size: 10 } }
                    }
                }
            }
        });

        // Hapus Event Logic
        function hapusEvent(id) {
            if(confirm('⚠ PERINGATAN: Apakah Anda yakin ingin menghapus event ini?')) {
                let formData = new FormData();
                formData.append('event_id', id);

                fetch('hapus-event.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); 
                    } else {
                        alert('Gagal menghapus: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan koneksi.');
                });
            }
        }
    </script>
</body>
</html>