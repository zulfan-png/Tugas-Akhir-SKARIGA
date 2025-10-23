<?php
include "koneksi.php";

$id = $_POST['id'];
$perusahaan = $_POST['perusahaan'];
$tipe_bus = $_POST['tipe_bus'];
$jenis = $_POST['jenis'];
$kapasitas = $_POST['kapasitas'];
$deskripsi = $_POST['deskripsi'];
$harga1 = $_POST['harga1'];
$harga2 = $_POST['harga2'];
$harga3 = $_POST['harga3'];
$harga4 = $_POST['harga4'];
$harga5 = $_POST['harga5'];
$harga6 = $_POST['harga6'];

// Proses fasilitas dari checkbox
$fasilitas = "";
if(isset($_POST['fasilitas'])) {
    $fasilitas = implode(", ", $_POST['fasilitas']);
}

$query = "UPDATE bus SET 
          perusahaan = '$perusahaan',
          `tipe bus` = '$tipe_bus',
          jenis = '$jenis',
          kapasitas = '$kapasitas',
          fasilitas = '$fasilitas',
          deskripsi = '$deskripsi',
          harga1 = '$harga1',
          harga2 = '$harga2',
          harga3 = '$harga3',
          harga4 = '$harga4',
          harga5 = '$harga5',
          harga6 = '$harga6'
          WHERE id = $id";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGUPDATE DATA');
    window.location.href = 'admin_bus.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGUPDATE DATA');
    window.location.href = 'admin_bus.php';
    </script>";
}
?>