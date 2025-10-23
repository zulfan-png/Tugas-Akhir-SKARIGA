<?php
include "koneksi.php";

$user = $_POST['user'];
$password = $_POST['password'];
$email = $_POST['email'];
$nomor_hp = $_POST['nomor_hp'];
$level = $_POST['level'];

$query = "INSERT INTO datauser (user, password, email, `nomor hp`, level) VALUES ('$user', '$password', '$email', '$nomor_hp', '$level')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENAMBAH DATA');
    window.location.href = 'admin_user.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENAMBAH DATA');
    window.location.href = 'admin_user.php';
    </script>";
}
?>