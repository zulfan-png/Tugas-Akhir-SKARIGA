<?php
include 'koneksi.php';
session_start();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit();
}

$booking_id = intval($_GET['id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Query data booking
$query = "SELECT * FROM pemesanan WHERE id = $booking_id";
if ($user_id > 0) {
    $query .= " AND user_id = $user_id";
}

$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
    $booking = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'booking' => [
            'jenis_paket' => $booking['jenis_paket'],
            'tanggal_berangkat' => $booking['tanggal_berangkat'],
            'jam_berangkat' => $booking['jam_berangkat'],
            'tanggal_kembali' => $booking['tanggal_kembali'],
            'lokasi_penjemputan' => $booking['lokasi_penjemputan'],
            'tujuan' => $booking['tujuan'],
            'jumlah_penumpang' => $booking['jumlah_penumpang'],
            'keterangan' => $booking['keterangan']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}
?>