<?php
// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql = 'localhost';
$user = 'root';
$pass = '';
$db = 'bus_management_web'; // Pastikan nama database benar

$connect = mysqli_connect($mysql, $user, $pass, $db);

// Check connection
if (!$connect) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($connect, "utf8mb4");

// if($connect) echo "Sukses Koneksi";
?>