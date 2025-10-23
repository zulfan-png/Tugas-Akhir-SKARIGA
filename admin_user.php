<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Admin Panel</title>
    <!-- Bootstrap 3.4.1 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome 4.7.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
</head>
<body class="skin-blue sidebar-mini">
    <div class="wrapper">
        <!-- Header -->
        <header class="main-header">
            <a href="index.php" class="logo">
                <span class="logo-mini"><b>A</b>DM</span>
                <span class="logo-lg"><b>Admin</b>Panel</span>
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
            </nav>
        </header>

        <!-- Sidebar -->
        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu" data-widget="tree">
                    <li class="header">MAIN NAVIGATION</li>
                    <li>
                        <a href="admin_bus.php">
                            <i class="fa fa-bus"></i> <span>Data Bus</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="admin_user.php">
                            <i class="fa fa-users"></i> <span>Data User</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-sign-out"></i> <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <h1>
                    Data User
                    <small>Management Data User</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Data User</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Daftar User</h3>
                                <div class="box-tools pull-right">
                                    <a href="admin_user_tambah.php" class="btn btn-sm btn-primary">
                                        <i class="fa fa-plus"></i> Tambah User
                                    </a>
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr class="bg-primary">
                                                <th>ID User</th>
                                                <th>Nama</th>
                                                <th>Password</th>
                                                <th>Email</th>
                                                <th>Nomor HP</th>
                                                <th>Level</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT * FROM datauser";
                                            $result = mysqli_query($connect, $query);

                                            if (mysqli_num_rows($result) > 0) {
                                                while($row = mysqli_fetch_array($result)){
                                            ?>
                                                <tr>
                                                    <td><?php echo $row['id'] ?></td>
                                                    <td><?php echo $row['user'] ?></td>
                                                    <td><?php echo substr($row['password'], 0, 10) . '...' ?></td>
                                                    <td><?php echo $row['email'] ?></td>
                                                    <td><?php echo $row['nomor hp'] ?></td>
                                                    <td>
                                                        <?php 
                                                        if ($row['level'] == 1) {
                                                            echo '<span class="label label-success">User</span>';
                                                        } elseif ($row['level'] == 2) {
                                                            echo '<span class="label label-primary">Admin</span>';
                                                        } else {
                                                            echo '<span class="label label-default">' . $row['level'] . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="admin_user_edit.php?id=<?php echo $row['id'] ?>" class="btn btn-xs btn-warning">
                                                                <i class="fa fa-edit"></i> Edit
                                                            </a>
                                                            <a href="admin_user_hapus.php?id=<?php echo $row['id'] ?>" class="btn btn-xs btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                                <i class="fa fa-trash"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>    
                                            <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="7" style="text-align: center; padding: 20px;">
                                                        <i class="fa fa-exclamation-circle"></i> Tidak ada data user
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

        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; 2024 BISATA.</strong> All rights reserved.
        </footer>
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