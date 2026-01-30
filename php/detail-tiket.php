<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah pengguna sudah login
$isLoggedIn = isset($_SESSION['user_id']); 
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

if ($event_id) {
    include_once '../connection/connect.php'; 
    try {
        $pdo = getDatabaseConnection();

        // Get Event Info (Optional: Untuk menampilkan judul event di summary)
        $stmtEvent = $pdo->prepare("SELECT title FROM events WHERE event_id = :event_id");
        $stmtEvent->execute(['event_id' => $event_id]);
        $eventData = $stmtEvent->fetch();
        $eventTitle = $eventData ? $eventData['title'] : 'Detail Event';

        if ($isLoggedIn) {
            $userId = $_SESSION['user_id'];
            $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE user_id = :user_id LIMIT 1");
            $stmtUser->execute(['user_id' => $userId]);
            $user = $stmtUser->fetch();
            $userName = $user ? htmlspecialchars($user['name']) : 'Pengguna';
            $userEmail = $user ? htmlspecialchars($user['email']) : '';
            $isNameEditable = empty($user['name']); 
        } else {
            $userName = '';
            $userEmail = '';
            $isNameEditable = true;
        }

        // Get Tickets
        $sql = "SELECT * FROM tickets WHERE event_id = :event_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['event_id' => $event_id]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Koneksi gagal: " . $e->getMessage());
    }
} else {
    die("Event ID tidak valid.");
}

$message = '';
if (!$isLoggedIn) {
    $message = '<i class="bi bi-info-circle me-1"></i> Silakan login untuk pengisian data otomatis.';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beli Tiket - <?php echo htmlspecialchars($eventTitle); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="../css/navbar_footer.css">
    <link rel="stylesheet" type="text/css" href="../css/detail-tiket.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <div class="mx-auto my-3 my-lg-0 w-100 w-lg-50 px-lg-4">
                        <form class="position-relative">
                            <input type="text" class="form-control search-bar" placeholder="Cari event lain..." readonly>
                            <button class="btn position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle" type="button" style="background: #ff6b6b; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class="bi bi-search text-white" style="font-size: 0.8rem;"></i>
                            </button>
                        </form>
                    </div>

                    <div class="d-flex align-items-center gap-3 justify-content-end">
                        <a href="../jelajah.php" class="nav-link">Jelajah</a>
                        <?php if (!$isLoggedIn): ?>
                            <a href="register.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">Daftar</a>
                            <a href="login.php" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold border-0">Masuk</a>
                        <?php else: ?>
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                                    <div class="profile-icon">
                                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                                    <li><h6 class="dropdown-header">Halo, <?php echo htmlspecialchars($userName); ?></h6></li>
                                    <li><a class="dropdown-item" href="riwayat.php">Tiket Saya</a></li>
                                    <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Keluar</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5 flex-grow-1">
        <div class="row gx-5">
            
            <div class="col-lg-8 mb-4">
                
                <ul class="nav nav-pills mb-4" id="bookingTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tiket-tab" data-bs-toggle="pill" data-bs-target="#tiket" type="button" role="tab">1. Pilih Tiket</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="biodata-tab" data-bs-toggle="pill" data-bs-target="#biodata" type="button" role="tab" disabled>2. Biodata</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="konfirmasi-tab" data-bs-toggle="pill" data-bs-target="#konfirmasi" type="button" role="tab" disabled>3. Konfirmasi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pembayaran-tab" data-bs-toggle="pill" data-bs-target="#pembayaran" type="button" role="tab" disabled>4. Bayar</button>
                    </li>
                </ul>

                <div class="tab-content" id="bookingTabContent">
                    
                    <div class="tab-pane fade show active content-card" id="tiket" role="tabpanel">
                        <h3 class="step-title">Pilih Kategori Tiket</h3>
                        
                        <?php if ($tickets): ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="ticket-item d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div>
                                        <div class="ticket-name"><?php echo htmlspecialchars($ticket['ticket_type']); ?></div>
                                        <div class="small text-muted mb-1"><?php echo $ticket['quantity_available']; ?> tiket tersisa</div>
                                        <div class="ticket-price">Rp <?php echo number_format($ticket['price'], 0, ',', '.'); ?></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="small text-muted">Jml:</label>
                                        <select class="form-select form-select-custom w-auto ticket-selector" 
                                                id="jumlahTiket<?php echo $ticket['ticket_id']; ?>"
                                                data-id="<?php echo $ticket['ticket_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($ticket['ticket_type']); ?>"
                                                data-price="<?php echo $ticket['price']; ?>">
                                            <?php 
                                            $maxDropdown = min($ticket['quantity_available'], 5); // Limit 5 per transaksi for UX
                                            for ($i = 0; $i <= $maxDropdown; $i++): 
                                            ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-gradient" id="btnToBiodata">Lanjut ke Biodata <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-ticket-perforated text-muted" style="font-size: 3rem;"></i>
                                <p class="mt-3 text-muted">Tiket Habis / Tidak Tersedia</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade content-card" id="biodata" role="tabpanel">
                        <h3 class="step-title">Lengkapi Data Pemesan</h3>
                        <p class="text-muted small mb-4"><?php echo $message; ?></p>
                        
                        <form id="biodataForm">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" 
                                       placeholder="Sesuai KTP/Identitas" 
                                       value="<?php echo $userName; ?>" 
                                       <?php echo $isLoggedIn && !$isNameEditable ? 'readonly' : ''; ?> required>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Aktif</label>
                                <input type="email" class="form-control" id="email" 
                                       placeholder="E-ticket akan dikirim kesini" 
                                       value="<?php echo $userEmail; ?>" 
                                       <?php echo $isLoggedIn ? 'readonly' : ''; ?> required>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="btnBackToTiket">Kembali</button>
                                <button type="submit" class="btn btn-gradient" id="btnToKonfirmasi">Lanjut Konfirmasi</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade content-card" id="konfirmasi" role="tabpanel">
                        <h3 class="step-title">Konfirmasi & Pembayaran</h3>
                        
                        <div class="alert alert-light border-0 bg-light p-3 rounded-4 mb-4">
                            <h6 class="fw-bold mb-2">Detail Pemesan:</h6>
                            <p class="mb-1"><i class="bi bi-person me-2 text-muted"></i> <span id="confirmName">-</span></p>
                            <p class="mb-0"><i class="bi bi-envelope me-2 text-muted"></i> <span id="confirmEmail">-</span></p>
                        </div>

                        <h6 class="fw-bold mb-3">Pilih Metode Pembayaran:</h6>
                        <div class="row g-2 mb-4">
                            <div class="col-6 col-md-4">
                                <div class="payment-option text-center" onclick="selectPayment(this, 'Transfer Bank')">
                                    <i class="bi bi-bank fs-3 d-block mb-1"></i>
                                    <small>Transfer Bank</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="payment-option text-center" onclick="selectPayment(this, 'E-Wallet')">
                                    <i class="bi bi-wallet2 fs-3 d-block mb-1"></i>
                                    <small>E-Wallet</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="payment-option text-center" onclick="selectPayment(this, 'QRIS')">
                                    <i class="bi bi-qr-code-scan fs-3 d-block mb-1"></i>
                                    <small>QRIS</small>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="selectedPaymentMethod" value="">

                        <div class="d-flex justify-content-between">
                            <button class="btn btn-outline-secondary rounded-pill px-4" id="btnBackToBiodata">Kembali</button>
                            <button class="btn btn-gradient" id="btnToPembayaran">Buat Pesanan</button>
                        </div>
                    </div>

                    <div class="tab-pane fade content-card text-center py-5" id="pembayaran" role="tabpanel">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;" id="loadingSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <i class="bi bi-check-circle-fill text-success d-none" style="font-size: 5rem;" id="successIcon"></i>
                        </div>
                        <h3 class="fw-bold" id="paymentTitle">Memproses...</h3>
                        <p class="text-muted" id="paymentDesc">Mohon tunggu sebentar, kami sedang membuat pesanan Anda.</p>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="summary-title">Ringkasan Pesanan</h5>
                    <div class="mb-3">
                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($eventTitle); ?></h6>
                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> Detail Event</small>
                    </div>
                    
                    <hr class="border-secondary opacity-25">
                    
                    <div id="selectedTicketsList" class="summary-list mb-3">
                        <p class="text-muted small fst-italic">Belum ada tiket dipilih.</p>
                    </div>
                    
                    <hr class="border-secondary opacity-25">
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted">Total Bayar</span>
                        <span class="summary-total" id="grandTotal">Rp 0</span>
                    </div>

                    <div class="mt-4">
                         <div class="d-flex align-items-center gap-2 p-2 rounded bg-light border">
                             <i class="bi bi-shield-check text-success fs-4"></i>
                             <small class="text-muted lh-sm">Jaminan tiket resmi & transaksi aman 100%</small>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6">
                        <h4 class="mb-3">BÉLI<span style="color: #ff6b6b;">TIKÉT</span>.</h4>
                        <p>Platform pembelian tiket event terpercaya. Temukan konser, seminar, dan workshop favoritmu dengan mudah.</p>
                        <div class="social-icons mt-4">
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-twitter-x"></i></a>
                            <a href="#"><i class="bi bi-facebook"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h5 class="mb-3">Bantuan</h5>
                        <ul>
                            <li><a href="#">Cara Pesan</a></li>
                            <li><a href="#">FAQ</a></li>
                            <li><a href="#">Hubungi Kami</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="mb-3">Pembayaran</h5>
                        <div class="p-3 bg-white rounded-3 d-inline-block">
                            <img src="../assets/images/logo_bsi.png" alt="Bank" style="height: 25px;">
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
    <script src="../javascript/navbar.js"></script>
    
    <script>
    // --- Logic UI Update Ringkasan ---
    const ticketSelectors = document.querySelectorAll('.ticket-selector');
    const selectedTicketsList = document.getElementById('selectedTicketsList');
    const grandTotalEl = document.getElementById('grandTotal');
    const btnToBiodata = document.getElementById('btnToBiodata');
const isUserLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

    // Helper: Format Rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    // Function to update summary
    function updateSummary() {
        let total = 0;
        let html = '';
        let hasTicket = false;

        ticketSelectors.forEach(select => {
            const qty = parseInt(select.value);
            const price = parseInt(select.dataset.price);
            const name = select.dataset.name;

            if (qty > 0) {
                hasTicket = true;
                const subtotal = qty * price;
                total += subtotal;
                html += `
                    <div class="d-flex justify-content-between mb-2">
                        <span>${name} <span class="text-muted">x${qty}</span></span>
                        <span class="fw-semibold">${formatRupiah(subtotal)}</span>
                    </div>
                `;
            }
        });

        if (hasTicket) {
            selectedTicketsList.innerHTML = html;
            btnToBiodata.disabled = false;
        } else {
            selectedTicketsList.innerHTML = '<p class="text-muted small fst-italic">Belum ada tiket dipilih.</p>';
            btnToBiodata.disabled = true;
        }

        grandTotalEl.innerText = formatRupiah(total);
    }

    // Attach listeners
    ticketSelectors.forEach(select => {
        select.addEventListener('change', updateSummary);
    });

    // Initial check
    updateSummary();

    // --- Tab Navigation Logic ---
    const triggerTab = (id) => {
        const el = document.querySelector(id);
        const tab = new bootstrap.Tab(el);
        tab.show();
        el.disabled = false; // Enable tab
    }

    // Step 1 -> 2
    btnToBiodata.addEventListener('click', () => {
        // Cek 1: Apakah tiket sudah dipilih?
        if(grandTotalEl.innerText === 'Rp 0') {
            alert('Silakan pilih minimal 1 tiket terlebih dahulu!'); 
            return;
        }

        // Cek 2: Apakah user sudah login? (LOGIKA BARU)
        if (!isUserLoggedIn) {
            const confirmLogin = confirm("Anda harus login untuk melanjutkan pembelian. Ingin masuk sekarang?");
            if (confirmLogin) {
                // Simpan URL saat ini agar nanti bisa redirect balik (opsional, tapi bagus untuk UX)
                // window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                
                // Redirect sederhana ke login
                window.location.href = 'login.php';
            }
            return; // Hentikan proses, jangan lanjut ke tab biodata
        }

        // Jika lolos semua cek, lanjut ke tab biodata
        triggerTab('#biodata-tab');
    });

    // Step 2 -> 1
    document.getElementById('btnBackToTiket').addEventListener('click', () => triggerTab('#tiket-tab'));

    // Step 2 -> 3
    document.getElementById('btnToKonfirmasi').addEventListener('click', (e) => {
        e.preventDefault();
        const nama = document.getElementById('nama').value;
        const email = document.getElementById('email').value;

        if(!nama || !email) {
            alert("Harap isi biodata lengkap!");
            return;
        }

        // Simpan ke Session Storage & Tampilkan di Konfirmasi
        sessionStorage.setItem('user_name', nama);
        sessionStorage.setItem('email', email);
        document.getElementById('confirmName').innerText = nama;
        document.getElementById('confirmEmail').innerText = email;

        triggerTab('#konfirmasi-tab');
    });

    // Step 3 -> 2
    document.getElementById('btnBackToBiodata').addEventListener('click', () => triggerTab('#biodata-tab'));

    // Payment Selection UI
    function selectPayment(el, method) {
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('selectedPaymentMethod').value = method;
    }

    // Step 3 -> 4 (Process Payment & DB)
    document.getElementById("btnToPembayaran").addEventListener("click", function() {
        const paymentMethod = document.getElementById('selectedPaymentMethod').value;
        if(!paymentMethod) {
            alert('Pilih metode pembayaran terlebih dahulu!');
            return;
        }

        // Show Loading Tab
        triggerTab('#pembayaran-tab');
        
        // Collect Data
        const validTickets = [];
        ticketSelectors.forEach(select => {
            const qty = parseInt(select.value);
            if (qty > 0) {
                validTickets.push({
                    ticket_id: select.dataset.id,
                    quantity: qty
                });
            }
        });

        // --- AJAX Process ---
        // 1. Update Stok
        fetch("../php/update_tickets.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ tickets: validTickets })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 2. Insert Order
                return fetch("../php/insert_order.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        tickets: validTickets,
                        user_name: sessionStorage.getItem('user_name'),
                        email: sessionStorage.getItem('email')
                    })
                });
            } else {
                throw new Error("Gagal update stok: " + data.message);
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Success UI
                document.getElementById('loadingSpinner').classList.add('d-none');
                document.getElementById('successIcon').classList.remove('d-none');
                document.getElementById('paymentTitle').innerText = "Pembayaran Berhasil!";
                document.getElementById('paymentDesc').innerText = "Terima kasih, tiket Anda telah terbit. Mengalihkan...";
                
                setTimeout(() => {
                    window.location.href = `http://localhost/pemweb_uas-main/php/riwayat.php`; // Redirect ke riwayat
                }, 2000);
            } else {
                throw new Error("Gagal buat order: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert(err.message);
            triggerTab('#konfirmasi-tab'); // Balik ke konfirmasi jika gagal
        });
    });
    </script>

</body>
</html>