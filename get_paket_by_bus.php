<?php
include 'koneksi.php';

if (isset($_GET['bus_id'])) {
    $bus_id = $_GET['bus_id'];
    
    $query = "SELECT jenis_harga FROM harga_bus WHERE bus_id = $bus_id GROUP BY jenis_harga";
    $result = mysqli_query($connect, $query);
    
    echo '<option value="">-- Pilih Jenis Paket --</option>';
    while($row = mysqli_fetch_array($result)) {
        echo '<option value="' . $row['jenis_harga'] . '">' . $row['jenis_harga'] . '</option>';
    }
} else {
    echo '<option value="">-- Pilih Bus Terlebih Dahulu --</option>';
}
?>