<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "SELECT * FROM datauser WHERE id = $id";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_array($result);

// Include header
include 'header.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Edit User
            <small>Form edit data user</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="admin_user.php">Data User</a></li>
            <li class="active">Edit User</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Form Edit User</h3>
                    </div>
                    <!-- /.box-header -->
                    <form action="admin_user_proses_edit.php" method="post">
                        <div class="box-body">
                            <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                            
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?php echo $row['nama_lengkap'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="<?php echo $row['username'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo $row['email'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Nomor HP</label>
                                <input type="text" name="nomor_hp" class="form-control" value="<?php echo $row['nomor_hp'] ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3"><?php echo $row['alamat'] ?? '' ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Level</label>
                                <select name="level" class="form-control" required>
                                    <option value="customer" <?php echo $row['level'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="admin" <?php echo $row['level'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="operator" <?php echo $row['level'] == 'operator' ? 'selected' : ''; ?>>Operator</option>
                                    <option value="supir" <?php echo $row['level'] == 'supir' ? 'selected' : ''; ?>>Supir</option>
                                </select>
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                            <a href="admin_user.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Batal</a>
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