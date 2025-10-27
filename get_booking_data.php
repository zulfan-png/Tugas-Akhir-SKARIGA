<?php
include 'koneksi.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM pemesanan WHERE id = $booking_id AND user_id = $user_id";
$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
    $booking = mysqli_fetch_assoc($result);
    echo json_encode(['success' => true, 'booking' => $booking]);
} else {
    echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan']);
}
?>