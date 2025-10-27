<?php
include 'koneksi.php';

// Ambil data dari form
$nama_lengkap = $_POST['nama_lengkap'];
$username = $_POST['username'];
$password = $_POST['password']; // Password tanpa hash/MD5
$email = $_POST['email'];
$alamat = $_POST['alamat'];
$nomor_hp = $_POST['nomor_hp'];

// Cek apakah username sudah ada
$check_query = "SELECT * FROM datauser WHERE username = '$username'";
$check_result = mysqli_query($connect, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    echo "<script>
    alert('Username sudah digunakan! Silakan gunakan username lain.');
    window.location.href = 'register.php';
    </script>";
    exit;
}

// Query untuk insert user baru (password disimpan tanpa hash)
$query = "INSERT INTO datauser (nama_lengkap, username, password, email, alamat, nomor_hp, level) 
          VALUES ('$nama_lengkap', '$username', '$password', '$email', '$alamat', '$nomor_hp', 'customer')";

if (mysqli_query($connect, $query)) {
    echo "<script>
    alert('Registrasi berhasil! Silakan login.');
    window.location.href = 'index.html';
    </script>";
} else {
    echo "<script>
    alert('Terjadi kesalahan: " . mysqli_error($connect) . "');
    window.location.href = 'register.php';
    </script>";
}

mysqli_close($connect);
?>