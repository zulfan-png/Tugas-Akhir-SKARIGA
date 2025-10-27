<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "SELECT b.*, p.nama_perusahaan 
          FROM bus b 
          LEFT JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
          WHERE b.id = $id";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_array($result);

// Ambil data perusahaan untuk dropdown
$query_perusahaan = "SELECT * FROM perusahaan_bus";
$result_perusahaan = mysqli_query($connect, $query_perusahaan);

// Pisahkan fasilitas menjadi array
$fasilitas_array = [];
if(!empty($row['fasilitas'])) {
    $fasilitas_array = explode(", ", $row['fasilitas']);
}

include 'header.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Edit Data Bus
            <small>Form edit data bus</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="admin_bus.php">Data Bus</a></li>
            <li class="active">Edit Data Bus</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Form Edit Data Bus</h3>
                    </div>
                    <!-- /.box-header -->
                    <form action="admin_busproses_edit.php" method="post">
                        <div class="box-body">
                            <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                            
                            <div class="form-group">
                                <label>Perusahaan:</label>
                                <select name="perusahaan_id" class="form-control" required>
                                    <option value="">Pilih Perusahaan</option>
                                    <?php while($perusahaan = mysqli_fetch_array($result_perusahaan)): ?>
                                        <option value="<?php echo $perusahaan['id'] ?>" 
                                            <?php echo $row['perusahaan_id'] == $perusahaan['id'] ? 'selected' : '' ?>>
                                            <?php echo $perusahaan['nama_perusahaan'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Tipe Bus:</label>
                                <input type="text" name="tipe_bus" class="form-control" value="<?php echo $row['tipe bus'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Jenis:</label>
                                <input type="text" name="jenis" class="form-control" value="<?php echo $row['jenis'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Kapasitas:</label>
                                <input type="number" name="kapasitas" class="form-control" value="<?php echo $row['kapasitas'] ?>" required>
                            </div>

                            <!-- Tambahkan field WhatsApp setelah field Status -->
                            <div class="form-group">
                                <label>Nomor WhatsApp:</label>
                                <input type="text" name="whatsapp_perusahaan" class="form-control" 
                                    value="<?php echo $row['whatsapp_perusahaan'] ?>" 
                                    placeholder="Contoh: +62123456789 (tanpa spasi, atau tanda baca)">
                            </div>

                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status" class="form-control" required>
                                    <option value="Tersedia" <?php echo $row['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                    <option value="Dalam Perawatan" <?php echo $row['status'] == 'Dalam Perawatan' ? 'selected' : '' ?>>Dalam Perawatan</option>
                                    <option value="Tidak Tersedia" <?php echo $row['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
                                </select>
                            </div>

                            <!-- Fasilitas section remains the same -->
                            <div class="form-group">
                                <label>Fasilitas:</label><br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="AC" <?php echo in_array("AC", $fasilitas_array) ? 'checked' : ''; ?>> AC</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Toilet" <?php echo in_array("Toilet", $fasilitas_array) ? 'checked' : ''; ?>> Toilet</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="TV" <?php echo in_array("TV", $fasilitas_array) ? 'checked' : ''; ?>> TV</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Karaoke" <?php echo in_array("Karaoke", $fasilitas_array) ? 'checked' : ''; ?>> Karaoke</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="WiFi" <?php echo in_array("WiFi", $fasilitas_array) ? 'checked' : ''; ?>> WiFi</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Charging Port" <?php echo in_array("Charging Port", $fasilitas_array) ? 'checked' : ''; ?>> Charging Port</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Reclining Seat" <?php echo in_array("Reclining Seat", $fasilitas_array) ? 'checked' : ''; ?>> Reclining Seat</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Bantal Selimut" <?php echo in_array("Bantal Selimut", $fasilitas_array) ? 'checked' : ''; ?>> Bantal Selimut</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Bagasi Luas" <?php echo in_array("Bagasi Luas", $fasilitas_array) ? 'checked' : ''; ?>> Bagasi Luas</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Snack" <?php echo in_array("Snack", $fasilitas_array) ? 'checked' : ''; ?>> Snack</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi:</label>
                                <textarea name="deskripsi" class="form-control" rows="3" required><?php echo $row['deskripsi'] ?></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Data</button>
                            <a href="admin_bus.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Batal</a>
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

<?php
include 'sidebar.php';
include 'footer.php';
?>