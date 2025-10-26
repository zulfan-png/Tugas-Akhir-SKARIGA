<?php
include "koneksi.php";

$nama_lengkap = $_POST['nama_lengkap'];
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$nomor_hp = $_POST['nomor_hp'];
$alamat = $_POST['alamat'];
$level = $_POST['level'];

$query = "INSERT INTO datauser (nama_lengkap, username, password, email, nomor_hp, alamat, level) 
          VALUES ('$nama_lengkap', '$username', '$password', '$email', '$nomor_hp', '$alamat', '$level')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENAMBAH DATA');
    window.location.href = 'admin_user.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENAMBAH DATA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_user.php';
    </script>";
}
?>