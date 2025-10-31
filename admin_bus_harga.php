<?php
include 'koneksi.php';

$bus_id = $_GET['id'];

// Ambil data bus
$query_bus = "SELECT b.*, p.nama_perusahaan 
              FROM bus b 
              LEFT JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
              WHERE b.id = $bus_id";
$result_bus = mysqli_query($connect, $query_bus);
$bus = mysqli_fetch_array($result_bus);

// Ambil harga bus
$query_harga = "SELECT * FROM harga_bus WHERE bus_id = $bus_id";
$result_harga = mysqli_query($connect, $query_harga);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Harga Bus - BISATA</title>
    <!-- Bootstrap 3.4.1 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome 4.7.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    
    <style>
        .harga-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            background: white;
        }
        .harga-table th, .harga-table td { 
            border: 1px solid #f4f4f4; 
            padding: 12px; 
            text-align: left; 
        }
        .harga-table th { 
            background-color: #f5f5f5; 
            font-weight: 600;
        }
        .harga-table tr:hover {
            background-color: #f9f9f9;
        }
        .form-inline .form-group {
            margin-right: 15px;
            margin-bottom: 15px;
        }
        .form-inline .form-control {
            width: 200px;
        }
        .form-inline label {
            margin-right: 5px;
            font-weight: normal;
        }
        .content-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content-header .breadcrumb {
            float: right;
            background: transparent;
            margin-top: 0;
            margin-bottom: 0;
            font-size: 12px;
            padding: 7px 5px;
            position: absolute;
            top: 15px;
            right: 10px;
            border-radius: 2px;
        }
        .box-header {
            border-bottom: 1px solid #f4f4f4;
        }
        .box-title {
            font-size: 18px;
            font-weight: 500;
        }
        .price-value {
            font-weight: 600;
            color: #3c8dbc;
        }
    </style>
</head>
<body class="skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Kelola Harga Bus
                    <small><?php echo $bus['nama_perusahaan'] ?> <?php echo $bus['tipe bus'] ?></small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="admin_bus.php"><i class="fa fa-bus"></i> Data Bus</a></li>
                    <li class="active">Kelola Harga</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Form Tambah Harga Box -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Tambah Harga Baru</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <form action="admin_busproses_tambah_harga.php" method="post">
                                    <input type="hidden" name="bus_id" value="<?php echo $bus_id ?>">
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label for="jenis_harga">Jenis Harga:</label>
                                            <select name="jenis_harga" class="form-control" required>
                                                <option value="Paket 6 Jam">Paket 6 Jam</option>
                                                <option value="Paket 12 Jam">Paket 12 Jam</option>
                                                <option value="Paket 24 Jam">Paket 24 Jam</option>
                                                <option value="Paket 6 Jam (Weekend)">Paket 6 Jam (Weekend)</option>
                                                <option value="Paket 12 Jam (Weekend)">Paket 12 Jam (Weekend)</option>
                                                <option value="Paket 24 Jam (Weekend)">Paket 24 Jam (Weekend)</option>
                                                <option value="Luar Kota">Luar Kota</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="harga">Harga:</label>
                                            <div class="input-group">
                                                <span class="input-group-addon">Rp</span>
                                                <input type="number" name="harga" class="form-control" step="0.01" placeholder="0" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="satuan">Satuan:</label>
                                            <input type="text" name="satuan" class="form-control" value="Paket" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Tambah Harga
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Daftar Harga Box -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Daftar Harga</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <?php if (mysqli_num_rows($result_harga) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="harga-table table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Jenis Harga</th>
                                                    <th>Harga</th>
                                                    <th>Satuan</th>
                                                    <th width="100">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($harga = mysqli_fetch_array($result_harga)): ?>
                                                    <tr>
                                                        <td><?php echo $harga['jenis_harga'] ?></td>
                                                        <td class="price-value">Rp <?php echo number_format($harga['harga'], 0, ',', '.') ?></td>
                                                        <td><?php echo $harga['satuan'] ?></td>
                                                        <td>
                                                            <a href="admin_busproses_hapus_harga.php?id=<?php echo $harga['id'] ?>&bus_id=<?php echo $bus_id ?>" 
                                                               class="btn btn-danger btn-sm" 
                                                               onclick="return confirm('Yakin ingin menghapus harga ini?')">
                                                               <i class="fa fa-trash"></i> Hapus
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> Belum ada harga untuk bus ini.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php include 'footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery 3.4.1 -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <!-- Bootstrap 3.4.1 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js"></script>
</body>
</html>