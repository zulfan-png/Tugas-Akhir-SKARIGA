<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "SELECT * FROM bus WHERE id = $id";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_array($result);

// Pisahkan fasilitas menjadi array
$fasilitas_array = [];
if(!empty($row['fasilitas'])) {
    $fasilitas_array = explode(", ", $row['fasilitas']);
}

// Include header
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
                                <input type="text" name="perusahaan" class="form-control" value="<?php echo $row['perusahaan'] ?>" required>
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

                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status" class="form-control" required>
                                    <option value="Tersedia" <?php echo $row['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                    <option value="Dipesan" <?php echo $row['status'] == 'Dipesan' ? 'selected' : '' ?>>Dipesan</option>
                                </select>
                            </div>

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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="box box-info">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Harga Weekday</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="form-group">
                                                <label>Harga 6 jam:</label>
                                                <input type="number" name="harga1" class="form-control" value="<?php echo $row['harga1'] ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Harga 12 jam:</label>
                                                <input type="number" name="harga2" class="form-control" value="<?php echo $row['harga2'] ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Harga 24 jam:</label>
                                                <input type="number" name="harga3" class="form-control" value="<?php echo $row['harga3'] ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="box box-success">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Harga Weekend</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="form-group">
                                                <label>Harga 6 jam:</label>
                                                <input type="number" name="harga4" class="form-control" value="<?php echo $row['harga4'] ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Harga 12 jam:</label>
                                                <input type="number" name="harga5" class="form-control" value="<?php echo $row['harga5'] ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Harga 24 jam:</label>
                                                <input type="number" name="harga6" class="form-control" value="<?php echo $row['harga6'] ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
// Include sidebar
include 'sidebar.php';

// Include footer
include 'footer.php';
?>