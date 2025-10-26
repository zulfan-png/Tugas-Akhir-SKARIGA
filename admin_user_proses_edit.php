<?php
include "koneksi.php";

$id = $_POST['id'];
$nama_lengkap = $_POST['nama_lengkap'];
$username = $_POST['username'];
$email = $_POST['email'];
$nomor_hp = $_POST['nomor_hp'];
$alamat = $_POST['alamat'];
$level = $_POST['level'];

// Jika password diisi, update password juga
if (!empty($_POST['password'])) {
    $password = $_POST['password'];
    $query = "UPDATE datauser SET 
              nama_lengkap = '$nama_lengkap', 
              username = '$username', 
              password = '$password', 
              email = '$email', 
              nomor_hp = '$nomor_hp', 
              alamat = '$alamat', 
              level = '$level',
              updated_at = CURRENT_TIMESTAMP 
              WHERE id = $id";
} else {
    $query = "UPDATE datauser SET 
              nama_lengkap = '$nama_lengkap', 
              username = '$username', 
              email = '$email', 
              nomor_hp = '$nomor_hp', 
              alamat = '$alamat', 
              level = '$level',
              updated_at = CURRENT_TIMESTAMP 
              WHERE id = $id";
}

$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGUPDATE DATA');
    window.location.href = 'admin_user.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGUPDATE DATA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_user.php';
    </script>";
}
?>