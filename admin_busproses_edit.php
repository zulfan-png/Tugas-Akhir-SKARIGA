<?php
include "koneksi.php";

$id = $_POST['id'];
$perusahaan_id = $_POST['perusahaan_id'];
$tipe_bus = $_POST['tipe_bus'];
$jenis = $_POST['jenis'];
$kapasitas = $_POST['kapasitas'];
$status = $_POST['status'];
$whatsapp_perusahaan = $_POST['whatsapp_perusahaan'];
$deskripsi = $_POST['deskripsi'];

// Proses fasilitas dari checkbox
$fasilitas = "";
if(isset($_POST['fasilitas'])) {
    $fasilitas = implode(", ", $_POST['fasilitas']);
}

$query = "UPDATE bus SET 
          perusahaan_id = '$perusahaan_id',
          `tipe bus` = '$tipe_bus',
          jenis = '$jenis',
          kapasitas = '$kapasitas',
          status = '$status',
          whatsapp_perusahaan = '$whatsapp_perusahaan',
          fasilitas = '$fasilitas',
          deskripsi = '$deskripsi',
          updated_at = CURRENT_TIMESTAMP
          WHERE id = $id";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "<script>
    alert('BERHASIL MENGUPDATE DATA');
    window.location.href = 'admin_bus.php';
    </script>";
} else {
    echo "<script>
    alert('GAGAL MENGUPDATE DATA: " . mysqli_error($connect) . "');
    window.location.href = 'admin_bus.php';
    </script>";
}
?>