<?php
include 'koneksi.php';

// Ambil data perusahaan untuk dropdown
$query_perusahaan = "SELECT * FROM perusahaan_bus";
$result_perusahaan = mysqli_query($connect, $query_perusahaan);

include 'header.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Tambah Data Bus
            <small>Form tambah data bus baru</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="admin_bus.php">Data Bus</a></li>
            <li class="active">Tambah Data Bus</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Form Tambah Data Bus</h3>
                    </div>
                    <!-- /.box-header -->
                    <form action="admin_busproses_tambah.php" method="post">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Perusahaan:</label>
                                <select name="perusahaan_id" class="form-control" required>
                                    <option value="">Pilih Perusahaan</option>
                                    <?php while($perusahaan = mysqli_fetch_array($result_perusahaan)): ?>
                                        <option value="<?php echo $perusahaan['id'] ?>">
                                            <?php echo $perusahaan['nama_perusahaan'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Tipe Bus:</label>
                                <input type="text" name="tipe_bus" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Jenis:</label>
                                <input type="text" name="jenis" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Kapasitas:</label>
                                <input type="number" name="kapasitas" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Status:</label>
                                <select name="status" class="form-control" required>
                                    <option value="Tersedia" selected>Tersedia</option>
                                    <option value="Dalam Perawatan">Dalam Perawatan</option>
                                    <option value="Tidak Tersedia">Tidak Tersedia</option>
                                </select>
                            </div>

                            <!-- Fasilitas section remains the same -->
                            <div class="form-group">
                                <label>Fasilitas:</label><br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="AC"> AC</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Toilet"> Toilet</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="TV"> TV</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Karaoke"> Karaoke</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="WiFi"> WiFi</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Charging Port"> Charging Port</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Reclining Seat"> Reclining Seat</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Bantal Selimut"> Bantal Selimut</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Bagasi Luas"> Bagasi Luas</label>
                                        </div>
                                        <div class="checkbox">
                                            <label><input type="checkbox" name="fasilitas[]" value="Snack"> Snack</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi:</label>
                                <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Data</button>
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