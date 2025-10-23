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

<?php include 'footer.php'; ?>