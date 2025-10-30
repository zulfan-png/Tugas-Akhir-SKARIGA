<?php
include 'koneksi.php';

if (isset($_GET['bus_id']) && isset($_GET['jenis_paket'])) {
    $bus_id = $_GET['bus_id'];
    $jenis_paket = $_GET['jenis_paket'];
    
    $query = "SELECT harga FROM harga_bus WHERE bus_id = $bus_id AND jenis_harga = '$jenis_paket' LIMIT 1";
    $result = mysqli_query($connect, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        echo json_encode([
            'success' => true,
            'harga' => $row['harga']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Harga tidak ditemukan untuk paket ini'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter tidak lengkap'
    ]);
}
?>