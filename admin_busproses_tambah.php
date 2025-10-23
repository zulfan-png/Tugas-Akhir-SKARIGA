[file name]: admin_busproses_tambah.php
[file content begin]
<?php
include "koneksi.php";

$perusahaan = $_POST['perusahaan'];
$tipe_bus = $_POST['tipe_bus'];
$jenis = $_POST['jenis'];
$kapasitas = $_POST['kapasitas'];
$status = $_POST['status'];
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

$query = "INSERT INTO bus (perusahaan, `tipe bus`, jenis, kapasitas, status, fasilitas, deskripsi, harga1, harga2, harga3, harga4, harga5, harga6) 
          VALUES ('$perusahaan', '$tipe_bus', '$jenis', '$kapasitas', '$status', '$fasilitas', '$deskripsi', '$harga1', '$harga2', '$harga3', '$harga4', '$harga5', '$harga6')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENAMBAH DATA');
    window.location.href = 'admin_bus.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENAMBAH DATA');
    window.location.href = 'admin_bus.php';
    </script>";
}
?>
[file content end]