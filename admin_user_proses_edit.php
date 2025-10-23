<?php
include "koneksi.php";

$id = $_POST['id'];
$user = $_POST['user'];
$email = $_POST['email'];
$nomor_hp = $_POST['nomor_hp'];
$level = $_POST['level'];

// Jika password diisi, update password juga
if (!empty($_POST['password'])) {
    $password = $_POST['password'];
    $query = "UPDATE datauser SET user = '$user', password = '$password', email = '$email', `nomor hp` = '$nomor_hp', level = '$level' WHERE id = $id";
} else {
    $query = "UPDATE datauser SET user = '$user', email = '$email', `nomor hp` = '$nomor_hp', level = '$level' WHERE id = $id";
}

$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGUPDATE DATA');
    window.location.href = 'admin_user.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGUPDATE DATA');
    window.location.href = 'admin_user.php';
    </script>";
}
?>