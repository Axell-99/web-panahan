<?php
include 'panggil.php';
include 'check_access.php';
requireAdmin();

// Get tournament ID from URL
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : 0;

if (!$tournament_id) {
    die("ID Tournament tidak valid!");
}

// Get tournament information
$tournament_result = $conn->query("SELECT * FROM tournaments WHERE id = $tournament_id");
if (!$tournament_result || $tournament_result->num_rows == 0) {
    die("Tournament tidak ditemukan!");
}
$tournament = $tournament_result->fetch_assoc();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_participant') {
        $participant_id = $_POST['participant_id'];
        $category_id = $_POST['category_id'];
        $payment_status = $_POST['payment_status'];
        $status = $_POST['status'];
        $notes = $_POST['notes'];
        
        // Check if participant already registered
        $check_sql = "SELECT id FROM tournament_participants WHERE tournament_id = ? AND participant_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $tournament_id, $participant_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Peserta sudah terdaftar di tournament ini!']);
            exit;
        }
        
        $sql = "INSERT INTO tournament_participants (tournament_id, participant_id, category_id, payment_status, status, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $tournament_id, $participant_id, $category_id, $payment_status, $status, $notes);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Peserta berhasil ditambahkan ke tournament']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambah peserta: ' . $conn->error]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'update_participant') {
        $tp_id = $_POST['tp_id'];
        $category_id = $_POST['category_id'];
        $payment_status = $_POST['payment_status'];
        $status = $_POST['status'];
        $notes = $_POST['notes'];
        $seed_number = $_POST['seed_number'];
        
        $sql = "UPDATE tournament_participants SET category_id=?, payment_status=?, status=?, notes=?, seed_number=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssii", $category_id, $payment_status, $status, $notes, $seed_number, $tp_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Data peserta berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal update data: ' . $conn->error]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'remove_participant') {
        $tp_id = $_POST['tp_id'];
        $sql = "DELETE FROM tournament_participants WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tp_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Peserta berhasil dihapus dari tournament']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus peserta: ' . $conn->error]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_participant') {
        $tp_id = $_POST['tp_id'];
        $sql = "SELECT tp.*, p.name as participant_name, c.name as category_name 
                FROM tournament_participants tp 
                LEFT JOIN participants p ON tp.participant_id = p.id 
                LEFT JOIN categories c ON tp.category_id = c.id 
                WHERE tp.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        echo json_encode($data);
        exit;
    }
}

// Get available participants (not yet registered in this tournament)
$available_participants_sql = "SELECT p.* FROM participants p 
                              WHERE p.status = 'active' 
                              AND p.id NOT IN (SELECT participant_id FROM tournament_participants WHERE tournament_id = $tournament_id)
                              ORDER BY p.name";
$available_participants = $conn->query($available_participants_sql);

// Get available categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");

// Get registered participants
$search = isset($_GET['q']) ? $_GET['q'] : '';
if ($search) {
    $participants_sql = "SELECT tp.*, p.name as participant_name, p.birthdate, p.gender, p.phone, 
                        c.name as category_name, YEAR(CURDATE()) - YEAR(p.birthdate) as age
                        FROM tournament_participants tp 
                        LEFT JOIN participants p ON tp.participant_id = p.id 
                        LEFT JOIN categories c ON tp.category_id = c.id 
                        WHERE tp.tournament_id = $tournament_id 
                        AND (p.name LIKE '%$search%' OR c.name LIKE '%$search%')
                        ORDER BY tp.registration_date DESC";
} else {
    $participants_sql = "SELECT tp.*, p.name as participant_name, p.birthdate, p.gender, p.phone, 
                        c.name as category_name, YEAR(CURDATE()) - YEAR(p.birthdate) as age
                        FROM tournament_participants tp 
                        LEFT JOIN participants p ON tp.participant_id = p.id 
                        LEFT JOIN categories c ON tp.category_id = c.id 
                        WHERE tp.tournament_id = $tournament_id 
                        ORDER BY tp.registration_date DESC";
}
$participants_result = $conn->query($participants_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Peserta Lomba - <?= htmlspecialchars($tournament['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-4">

    <div class="mb-4">
        <h5><i class="fas fa-users"></i> Peserta Lomba</h5>
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-trophy"></i> <?= htmlspecialchars($tournament['name']); ?></h6>
                <p class="card-text">
                    <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($tournament['start_date'])); ?> - <?= date('d/m/Y', strtotime($tournament['end_date'])); ?><br>
                    <strong>Lokasi:</strong> <?= htmlspecialchars($tournament['location'] ?? '-'); ?><br>
                    <strong>Status:</strong> 
                    <?php
                    $statusClass = '';
                    switch($tournament['status']) {
                        case 'draft': $statusClass = 'secondary'; break;
                        case 'registration': $statusClass = 'info'; break;
                        case 'ongoing': $statusClass = 'warning'; break;
                        case 'completed': $statusClass = 'success'; break;
                        case 'cancelled': $statusClass = 'danger'; break;
                    }
                    ?>
                    <span class="badge bg-<?= $statusClass; ?>"><?= ucfirst($tournament['status']); ?></span>
                </p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                <i class="fas fa-plus"></i> Tambah Peserta
            </button>
            <a href="kegiatan.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Kegiatan
            </a>
        </div>
        <form class="d-flex" method="get">
            <input type="hidden" name="tournament_id" value="<?= $tournament_id; ?>">
            <input class="form-control me-2" type="search" name="q" placeholder="Cari peserta..." value="<?= htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Peserta</th>
                    <th>Umur</th>
                    <th>Gender</th>
                    <th>Kategori</th>
                    <th>Status Pembayaran</th>
                    <th>Status</th>
                    <th>Seed</th>
                    <th>Tanggal Daftar</th>
                    <th style="width: 200px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = $participants_result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['participant_name']); ?></td>
                    <td><?= $row['age']; ?> tahun</td>
                    <td>
                        <span class="badge bg-<?= $row['gender'] == 'Laki-laki' ? 'primary' : 'danger'; ?>">
                            <?= $row['gender']; ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['category_name']); ?></td>
                    <td>
                        <?php
                        $paymentClass = '';
                        switch($row['payment_status']) {
                            case 'pending': $paymentClass = 'warning'; break;
                            case 'paid': $paymentClass = 'success'; break;
                            case 'refunded': $paymentClass = 'secondary'; break;
                        }
                        ?>
                        <span class="badge bg-<?= $paymentClass; ?>"><?= ucfirst($row['payment_status']); ?></span>
                    </td>
                    <td>
                        <?php
                        $statusClass = '';
                        switch($row['status']) {
                            case 'registered': $statusClass = 'info'; break;
                            case 'confirmed': $statusClass = 'success'; break;
                            case 'withdrew': $statusClass = 'warning'; break;
                            case 'disqualified': $statusClass = 'danger'; break;
                        }
                        ?>
                        <span class="badge bg-<?= $statusClass; ?>"><?= ucfirst($row['status']); ?></span>
                    </td>
                    <td><?= $row['seed_number'] ?? '-'; ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['registration_date'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editParticipant(<?= $row['id']; ?>)" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="removeParticipant(<?= $row['id']; ?>)" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if ($participants_result->num_rows == 0): ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i><br>
                        <h6>Belum ada peserta terdaftar</h6>
                        <p class="text-muted">Klik tombol "Tambah Peserta" untuk mendaftarkan peserta baru</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Participant Modal -->
    <div class="modal fade" id="addParticipantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Peserta ke Tournament</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addParticipantForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_participant">
                        
                        <div class="mb-3">
                            <label for="participant_id" class="form-label">Pilih Peserta *</label>
                            <select class="form-control" id="participant_id" name="participant_id" required>
                                <option value="">-- Pilih Peserta --</option>
                                <?php while ($participant = $available_participants->fetch_assoc()): ?>
                                <option value="<?= $participant['id']; ?>">
                                    <?= htmlspecialchars($participant['name']); ?> 
                                    (<?= $participant['gender']; ?>, 
                                    <?= (date('Y') - date('Y', strtotime($participant['birthdate']))); ?> tahun)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?= $category['id']; ?>">
                                    <?= htmlspecialchars($category['name']); ?>
                                    (<?= $category['min_age']; ?>-<?= $category['max_age']; ?> tahun)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Status Pembayaran</label>
                                    <select class="form-control" id="payment_status" name="payment_status">
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Peserta</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="registered">Registered</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="withdrew">Withdrew</option>
                                        <option value="disqualified">Disqualified</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Tambah Peserta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Participant Modal -->
    <div class="modal fade" id="editParticipantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Peserta Tournament</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editParticipantForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_participant">
                        <input type="hidden" id="edit_tp_id" name="tp_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Peserta</label>
                            <input type="text" class="form-control" id="edit_participant_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="edit_category_id" class="form-label">Kategori *</label>
                            <select class="form-control" id="edit_category_id" name="category_id" required>
                                <?php 
                                // Reset categories result
                                $categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
                                while ($category = $categories->fetch_assoc()): 
                                ?>
                                <option value="<?= $category['id']; ?>">
                                    <?= htmlspecialchars($category['name']); ?>
                                    (<?= $category['min_age']; ?>-<?= $category['max_age']; ?> tahun)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_payment_status" class="form-label">Status Pembayaran</label>
                                    <select class="form-control" id="edit_payment_status" name="payment_status">
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status Peserta</label>
                                    <select class="form-control" id="edit_status" name="status">
                                        <option value="registered">Registered</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="withdrew">Withdrew</option>
                                        <option value="disqualified">Disqualified</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_seed_number" class="form-label">Seed Number</label>
                                    <input type="number" class="form-control" id="edit_seed_number" name="seed_number" min="1">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function editParticipant(tpId) {
            $.post('', {action: 'get_participant', tp_id: tpId}, function(response) {
                const data = JSON.parse(response);
                
                document.getElementById('edit_tp_id').value = data.id;
                document.getElementById('edit_participant_name').value = data.participant_name;
                document.getElementById('edit_category_id').value = data.category_id;
                document.getElementById('edit_payment_status').value = data.payment_status;
                document.getElementById('edit_status').value = data.status;
                document.getElementById('edit_seed_number').value = data.seed_number || '';
                document.getElementById('edit_notes').value = data.notes || '';
                
                $('#editParticipantModal').modal('show');
            });
        }

        function removeParticipant(tpId) {
            if (confirm('Yakin ingin menghapus peserta ini dari tournament?')) {
                $.post('', {action: 'remove_participant', tp_id: tpId}, function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert(result.message);
                    }
                });
            }
        }

        document.getElementById('addParticipantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert(result.message);
                        $('#addParticipantModal').modal('hide');
                        location.reload();
                    } else {
                        alert(result.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat memproses data');
                }
            });
        });

        document.getElementById('editParticipantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert(result.message);
                        $('#editParticipantModal').modal('hide');
                        location.reload();
                    } else {
                        alert(result.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat memproses data');
                }
            });
        });
    </script>

</body>
</html>