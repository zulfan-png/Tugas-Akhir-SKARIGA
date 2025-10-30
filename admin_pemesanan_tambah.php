<?php
// admin_pemesanan_tambah.php
include 'koneksi.php';
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data customer untuk dropdown
$customer_query = "SELECT id, nama_lengkap, nomor_hp FROM datauser WHERE level = 'customer' ORDER BY nama_lengkap";
$customer_result = mysqli_query($connect, $customer_query);

// Fungsi untuk menghitung jumlah hari
function hitungJumlahHari($tanggal_berangkat, $tanggal_kembali) {
    if (empty($tanggal_kembali)) {
        return 1; // Jika tidak ada tanggal kembali, dianggap 1 hari
    }
    
    $start = new DateTime($tanggal_berangkat);
    $end = new DateTime($tanggal_kembali);
    $interval = $start->diff($end);
    
    return $interval->days + 1; // +1 karena termasuk hari berangkat
}

// Proses form tambah pemesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pemesanan'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $nomor_hp = $_POST['nomor_hp'];
    $bus_id = $_POST['bus_id'];
    $jenis_paket = $_POST['jenis_paket'];
    $tanggal_berangkat = $_POST['tanggal_berangkat'];
    $jam_berangkat = $_POST['jam_berangkat'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $lokasi_penjemputan = $_POST['lokasi_penjemputan'];
    $tujuan = $_POST['tujuan'];
    $jumlah_penumpang = $_POST['jumlah_penumpang'];
    $keterangan = $_POST['keterangan'];
    $total_harga = $_POST['total_harga'];
    $status = $_POST['status'];
    
    // Validasi data
    $errors = [];
    
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap harus diisi";
    if (empty($nomor_hp)) $errors[] = "Nomor HP harus diisi";
    if (empty($bus_id)) $errors[] = "Pilih bus";
    if (empty($jenis_paket)) $errors[] = "Jenis paket harus diisi";
    if (empty($tanggal_berangkat)) $errors[] = "Tanggal berangkat harus diisi";
    if (empty($jam_berangkat)) $errors[] = "Jam berangkat harus diisi";
    if (empty($lokasi_penjemputan)) $errors[] = "Lokasi penjemputan harus diisi";
    if (empty($tujuan)) $errors[] = "Tujuan harus diisi";
    if (empty($jumlah_penumpang) || $jumlah_penumpang < 1) $errors[] = "Jumlah penumpang harus diisi";
    if (empty($total_harga) || $total_harga < 0) $errors[] = "Total harga harus diisi";
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        // Cek apakah customer sudah ada di database
        $user_query = "SELECT id FROM datauser WHERE nama_lengkap = '$nama_lengkap' AND nomor_hp = '$nomor_hp' AND level = 'customer'";
        $user_result = mysqli_query($connect, $user_query);
        
        if (mysqli_num_rows($user_result) > 0) {
            // Customer sudah ada, gunakan user_id yang ada
            $user_data = mysqli_fetch_array($user_result);
            $user_id = $user_data['id'];
        } else {
            // Customer baru, buat user baru
            $username = strtolower(str_replace(' ', '', $nama_lengkap)) . rand(100, 999);
            $email = $username . '@customer.local';
            $default_password = password_hash('123456', PASSWORD_DEFAULT);
            
            $insert_user = "INSERT INTO datauser (nama_lengkap, username, password, email, nomor_hp, level) 
                           VALUES ('$nama_lengkap', '$username', '$default_password', '$email', '$nomor_hp', 'customer')";
            
            if (mysqli_query($connect, $insert_user)) {
                $user_id = mysqli_insert_id($connect);
            } else {
                $error_message = "Gagal membuat user baru: " . mysqli_error($connect);
            }
        }
        
        if (isset($user_id)) {
            $query = "INSERT INTO pemesanan (
                        user_id, bus_id, jenis_paket, tanggal_berangkat, jam_berangkat, 
                        tanggal_kembali, lokasi_penjemputan, tujuan, jumlah_penumpang, 
                        keterangan, total_harga, status, permintaan_edit, permintaan_batal
                      ) VALUES (
                        '$user_id', '$bus_id', '$jenis_paket', '$tanggal_berangkat', '$jam_berangkat',
                        '$tanggal_kembali', '$lokasi_penjemputan', '$tujuan', '$jumlah_penumpang',
                        '$keterangan', '$total_harga', '$status', 'Tidak', 'Tidak'
                      )";
            
            if (mysqli_query($connect, $query)) {
                $success_message = "Pemesanan berhasil ditambahkan!";
                
                // Reset form values
                $_POST = array();
            } else {
                $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
            }
        }
    } else {
        $error_message = "Terjadi kesalahan:<br>" . implode("<br>", $errors);
    }
}

// Fungsi untuk mendapatkan paket berdasarkan bus
function getPaketByBus($bus_id, $connect) {
    $query = "SELECT jenis_harga FROM harga_bus WHERE bus_id = $bus_id GROUP BY jenis_harga";
    $result = mysqli_query($connect, $query);
    
    $paket = [];
    while($row = mysqli_fetch_array($result)) {
        $paket[] = $row['jenis_harga'];
    }
    
    return $paket;
}

// Fungsi untuk mendapatkan harga berdasarkan bus dan jenis paket
function getHargaByPaket($bus_id, $jenis_paket, $connect) {
    $query = "SELECT harga FROM harga_bus WHERE bus_id = $bus_id AND jenis_harga = '$jenis_paket' LIMIT 1";
    $result = mysqli_query($connect, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        return $row['harga'];
    }
    
    return 0;
}
?>

<?php include 'header.php'; ?>

<?php include 'sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <h1>
            Tambah Pemesanan
            <small>Input pemesanan untuk user datang langsung</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="admin_booking.php">Management Pesanan</a></li>
            <li class="active">Tambah Pemesanan</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Form Tambah Pemesanan</h3>
                    </div>
                    <!-- /.box-header -->
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible" style="margin: 15px;">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-check"></i> Sukses!</h4>
                            <?php echo $success_message; ?>
                            <br>
                            <div class="btn-group mt-1">
                                <a href="admin_booking.php" class="btn btn-primary btn-sm">
                                    <i class="fa fa-list"></i> Kembali ke Management Pesanan
                                </a>
                                <a href="admin_pemesanan_tambah.php" class="btn btn-success btn-sm">
                                    <i class="fa fa-plus"></i> Tambah Pemesanan Baru
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible" style="margin: 15px;">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Error!</h4>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="pemesananForm">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Lengkap Customer *</label>
                                        <input type="text" name="nama_lengkap" class="form-control" 
                                               value="<?php echo isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : ''; ?>" 
                                               placeholder="Masukkan nama lengkap customer" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nomor HP *</label>
                                        <input type="text" name="nomor_hp" class="form-control" 
                                               value="<?php echo isset($_POST['nomor_hp']) ? $_POST['nomor_hp'] : ''; ?>" 
                                               placeholder="Contoh: 081234567890" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pilih Bus *</label>
                                        <select name="bus_id" class="form-control" required id="bus_select">
                                            <option value="">-- Pilih Bus --</option>
                                            <?php 
                                            $bus_query = "SELECT b.*, p.nama_perusahaan FROM bus b 
                                                         JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
                                                         WHERE b.status = 'Tersedia' 
                                                         ORDER BY p.nama_perusahaan, b.`tipe bus`";
                                            $bus_result = mysqli_query($connect, $bus_query);
                                            while($bus = mysqli_fetch_array($bus_result)): 
                                            ?>
                                                <option value="<?php echo $bus['id'] ?>" 
                                                    data-kapasitas="<?php echo $bus['kapasitas'] ?>"
                                                    <?php echo (isset($_POST['bus_id']) && $_POST['bus_id'] == $bus['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $bus['nama_perusahaan'] ?> - <?php echo $bus['tipe bus'] ?> (Kapasitas: <?php echo $bus['kapasitas'] ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <small class="text-muted" id="kapasitas_info"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Paket *</label>
                                        <select name="jenis_paket" class="form-control" required id="jenis_paket_select">
                                            <option value="">-- Pilih Bus Terlebih Dahulu --</option>
                                        </select>
                                        <small class="text-muted" id="harga_info"></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Berangkat *</label>
                                        <input type="date" name="tanggal_berangkat" class="form-control" 
                                               value="<?php echo isset($_POST['tanggal_berangkat']) ? $_POST['tanggal_berangkat'] : ''; ?>" 
                                               min="<?php echo date('Y-m-d'); ?>" required id="tanggal_berangkat">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jam Berangkat *</label>
                                        <input type="time" name="jam_berangkat" class="form-control" 
                                               value="<?php echo isset($_POST['jam_berangkat']) ? $_POST['jam_berangkat'] : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Kembali</label>
                                        <input type="date" name="tanggal_kembali" class="form-control" 
                                               value="<?php echo isset($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : ''; ?>"
                                               min="<?php echo date('Y-m-d'); ?>" id="tanggal_kembali">
                                        <small class="text-muted">Kosongkan jika sama dengan tanggal berangkat (pulang pergi hari yang sama)</small>
                                        <div id="info_hari" style="margin-top: 5px; font-weight: bold; color: #007bff;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jumlah Penumpang *</label>
                                        <input type="number" name="jumlah_penumpang" class="form-control" 
                                               value="<?php echo isset($_POST['jumlah_penumpang']) ? $_POST['jumlah_penumpang'] : ''; ?>" 
                                               min="1" max="100" required id="jumlah_penumpang">
                                        <small class="text-muted" id="kapasitas_warning" style="color: red; display: none;">
                                            Jumlah penumpang melebihi kapasitas bus!
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Lokasi Penjemputan *</label>
                                <input type="text" name="lokasi_penjemputan" class="form-control" 
                                       value="<?php echo isset($_POST['lokasi_penjemputan']) ? $_POST['lokasi_penjemputan'] : ''; ?>" 
                                       placeholder="Contoh: Terminal Bus Kota, Alamat lengkap..." required>
                            </div>
                            
                            <div class="form-group">
                                <label>Tujuan *</label>
                                <input type="text" name="tujuan" class="form-control" 
                                       value="<?php echo isset($_POST['tujuan']) ? $_POST['tujuan'] : ''; ?>" 
                                       placeholder="Contoh: Bandung, Yogyakarta, atau alamat tujuan..." required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Total Harga (Rp) *</label>
                                        <input type="number" name="total_harga" class="form-control" 
                                               value="<?php echo isset($_POST['total_harga']) ? $_POST['total_harga'] : ''; ?>" 
                                               min="0" step="1000" placeholder="0" required id="total_harga" readonly>
                                        <small class="text-muted">Harga akan terisi otomatis berdasarkan paket dan jumlah hari</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Pemesanan *</label>
                                        <select name="status" class="form-control" required>
                                            <option value="Menunggu Konfirmasi" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Menunggu Konfirmasi') ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                                            <option value="Dikonfirmasi" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Dikonfirmasi') ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                            <option value="Dibatalkan" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Keterangan Tambahan</label>
                                <textarea name="keterangan" class="form-control" rows="3" 
                                          placeholder="Keterangan khusus, permintaan tambahan, dll."><?php echo isset($_POST['keterangan']) ? $_POST['keterangan'] : ''; ?></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        
                        <div class="box-footer">
                            <button type="submit" name="tambah_pemesanan" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Pemesanan
                            </button>
                            <a href="admin_booking.php" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Kembali ke Management Pesanan
                            </a>
                        </div>
                    </form>
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<script>
// Fungsi untuk memuat paket berdasarkan bus yang dipilih
function loadPaketByBus(busId) {
    if (busId) {
        // AJAX request untuk mendapatkan paket berdasarkan bus
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_paket_by_bus.php?bus_id=' + busId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var paketSelect = document.getElementById('jenis_paket_select');
                paketSelect.innerHTML = xhr.responseText;
                
                // Reset harga
                document.getElementById('total_harga').value = '';
                document.getElementById('harga_info').textContent = '';
                sessionStorage.removeItem('hargaPerHari');
            }
        };
        xhr.send();
    } else {
        var paketSelect = document.getElementById('jenis_paket_select');
        paketSelect.innerHTML = '<option value="">-- Pilih Bus Terlebih Dahulu --</option>';
        document.getElementById('total_harga').value = '';
        document.getElementById('harga_info').textContent = '';
        sessionStorage.removeItem('hargaPerHari');
    }
}

// Fungsi untuk memuat harga berdasarkan paket yang dipilih
function loadHargaByPaket(busId, jenisPaket) {
    if (busId && jenisPaket) {
        // AJAX request untuk mendapatkan harga berdasarkan paket
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_harga_by_paket.php?bus_id=' + busId + '&jenis_paket=' + encodeURIComponent(jenisPaket), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Simpan harga per hari
                    var hargaPerHari = response.harga;
                    sessionStorage.setItem('hargaPerHari', hargaPerHari);
                    
                    // Hitung total harga berdasarkan jumlah hari
                    hitungTotalHarga();
                } else {
                    document.getElementById('total_harga').value = '';
                    document.getElementById('harga_info').textContent = response.message;
                    document.getElementById('harga_info').style.color = 'red';
                    sessionStorage.removeItem('hargaPerHari');
                }
            }
        };
        xhr.send();
    }
}

// Fungsi untuk menghitung jumlah hari
function hitungJumlahHari() {
    var tanggalBerangkat = document.getElementById('tanggal_berangkat').value;
    var tanggalKembali = document.getElementById('tanggal_kembali').value;
    var infoHari = document.getElementById('info_hari');
    
    if (!tanggalBerangkat) {
        infoHari.textContent = '';
        return 1;
    }
    
    if (!tanggalKembali) {
        infoHari.textContent = '1 hari (pulang pergi)';
        return 1;
    }
    
    var start = new Date(tanggalBerangkat);
    var end = new Date(tanggalKembali);
    
    // Validasi tanggal kembali tidak boleh sebelum tanggal berangkat
    if (end < start) {
        infoHari.textContent = 'Tanggal kembali tidak boleh sebelum tanggal berangkat!';
        infoHari.style.color = 'red';
        return 0;
    }
    
    // Hitung selisih hari
    var timeDiff = end.getTime() - start.getTime();
    var jumlahHari = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 karena termasuk hari berangkat
    
    infoHari.textContent = jumlahHari + ' hari';
    infoHari.style.color = 'green';
    
    return jumlahHari;
}

// Fungsi untuk menghitung total harga
function hitungTotalHarga() {
    var hargaPerHari = sessionStorage.getItem('hargaPerHari');
    var jumlahHari = hitungJumlahHari();
    
    if (hargaPerHari && jumlahHari > 0) {
        var totalHarga = hargaPerHari * jumlahHari;
        document.getElementById('total_harga').value = totalHarga;
        document.getElementById('harga_info').innerHTML = 
            'Harga per hari: Rp ' + formatRupiah(hargaPerHari) + 
            ' × ' + jumlahHari + ' hari = Rp ' + formatRupiah(totalHarga);
        document.getElementById('harga_info').style.color = 'green';
    } else if (hargaPerHari) {
        document.getElementById('harga_info').textContent = 'Harga per hari: Rp ' + formatRupiah(hargaPerHari);
        document.getElementById('harga_info').style.color = 'blue';
    }
}

// Format angka ke format Rupiah
function formatRupiah(angka) {
    if (!angka) return '0';
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Event listener untuk perubahan bus
document.getElementById('bus_select').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var kapasitas = selectedOption.getAttribute('data-kapasitas');
    var busId = this.value;
    
    // Tampilkan informasi kapasitas
    if (kapasitas) {
        document.getElementById('kapasitas_info').textContent = 'Kapasitas bus: ' + kapasitas + ' penumpang';
        document.getElementById('kapasitas_info').style.color = 'blue';
    } else {
        document.getElementById('kapasitas_info').textContent = '';
    }
    
    // Muat paket berdasarkan bus
    loadPaketByBus(busId);
    
    checkCapacity();
});

// Event listener untuk perubahan paket
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'jenis_paket_select') {
        var busId = document.getElementById('bus_select').value;
        var jenisPaket = e.target.value;
        loadHargaByPaket(busId, jenisPaket);
    }
});

// Event listener untuk perubahan tanggal
document.getElementById('tanggal_berangkat').addEventListener('change', function() {
    var tanggalKembali = document.getElementById('tanggal_kembali');
    // Set min date untuk tanggal kembali sama dengan tanggal berangkat
    if (this.value) {
        tanggalKembali.min = this.value;
    }
    hitungTotalHarga();
});

document.getElementById('tanggal_kembali').addEventListener('change', function() {
    hitungTotalHarga();
});

// Validasi jumlah penumpang tidak melebihi kapasitas
document.getElementById('jumlah_penumpang').addEventListener('input', checkCapacity);

function checkCapacity() {
    var busSelect = document.getElementById('bus_select');
    var jumlahPenumpang = document.getElementById('jumlah_penumpang');
    var warning = document.getElementById('kapasitas_warning');
    
    if (busSelect.value && jumlahPenumpang.value) {
        var selectedOption = busSelect.options[busSelect.selectedIndex];
        var kapasitas = parseInt(selectedOption.getAttribute('data-kapasitas'));
        var penumpang = parseInt(jumlahPenumpang.value);
        
        if (penumpang > kapasitas) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    } else {
        warning.style.display = 'none';
    }
}

// Trigger pada saat halaman dimuat
window.onload = function() {
    var busSelect = document.getElementById('bus_select');
    if (busSelect.value) {
        busSelect.dispatchEvent(new Event('change'));
    }
    
    // Set min date untuk tanggal kembali
    var tanggalBerangkat = document.getElementById('tanggal_berangkat');
    var tanggalKembali = document.getElementById('tanggal_kembali');
    if (tanggalBerangkat.value) {
        tanggalKembali.min = tanggalBerangkat.value;
    }
    
    // Hitung total harga jika ada data yang sudah terisi
    hitungTotalHarga();
};
</script>