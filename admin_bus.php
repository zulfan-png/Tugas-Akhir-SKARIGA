[file name]: admin_bus.php
[file content begin]
<?php
include 'koneksi.php';
session_start();
?>

<?php include 'header.php'; ?>

<?php include 'sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <h1>
            Data Bus
            <small>Management Data Bus</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Data Bus</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Daftar Bus</h3>
                        <div class="box-tools pull-right">
                            <a href="admin_bustambah.php" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Tambah Data Bus
                            </a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-primary">    
                                        <th>Perusahaan</th>
                                        <th>Tipe Bus</th>
                                        <th>Jenis</th>
                                        <th>Kapasitas</th>
                                        <th>Status</th>
                                        <th>Fasilitas</th>
                                        <th>Deskripsi</th>
                                        <th>Gambar</th>
                                        <th>Harga Weekday</th>
                                        <th>Harga Weekend</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT b.*, 
                                             (SELECT gambar_url FROM bus_gambar WHERE bus_id = b.id LIMIT 1) as gambar_utama 
                                             FROM bus b";
                                    $result = mysqli_query($connect, $query);

                                    if (mysqli_num_rows($result) > 0) {
                                        while($row = mysqli_fetch_array($result)){
                                    ?>
                                        <tr>
                                            <td><?php echo $row['perusahaan'] ?></td>
                                            <td><?php echo $row['tipe bus'] ?></td>
                                            <td><?php echo $row['jenis'] ?></td>
                                            <td><?php echo $row['kapasitas'] ?></td>
                                            <td>
                                                <span class="label label-<?php echo $row['status'] == 'Tersedia' ? 'success' : 'warning' ?>">
                                                    <?php echo $row['status'] ?>
                                                </span>
                                                <br>
                                            </td>
                                            <td><?php echo substr($row['fasilitas'], 0, 30) . '...' ?></td>
                                            <td><?php echo substr($row['deskripsi'], 0, 30) . '...' ?></td>
                                            <td>
                                                <?php if(!empty($row['gambar_utama'])): ?>
                                                    <img src="uploads/<?php echo $row['gambar_utama'] ?>" class="gambar-thumb">
                                                <?php else: ?>
                                                    <span class="no-image">No Image</span>
                                                <?php endif; ?>
                                                <a href="admin_bus_gambar.php?id=<?php echo $row['id'] ?>" class="btn btn-xs btn-info btn-gambar">
                                                    <i class="fa fa-image"></i> Kelola
                                                </a>
                                            </td>
                                            <td>
                                                <small>
                                                    6j: Rp <?php echo number_format($row['harga1'], 0, ',', '.') ?><br>
                                                    12j: Rp <?php echo number_format($row['harga2'], 0, ',', '.') ?><br>
                                                    24j: Rp <?php echo number_format($row['harga3'], 0, ',', '.') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    6j: Rp <?php echo number_format($row['harga4'], 0, ',', '.') ?><br>
                                                    12j: Rp <?php echo number_format($row['harga5'], 0, ',', '.') ?><br>
                                                    24j: Rp <?php echo number_format($row['harga6'], 0, ',', '.') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="admin_busedit.php?id=<?php echo $row['id'] ?>" class="btn btn-xs btn-warning">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="admin_busproses_hapus.php?id=<?php echo $row['id'] ?>" class="btn btn-xs btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>    
                                    <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="11" style="text-align: center; padding: 20px;">
                                                <i class="fa fa-exclamation-circle"></i> Tidak ada data bus
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>
[file content end] 