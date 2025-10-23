<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "DELETE FROM bus WHERE id = $id";

if (mysqli_query($connect, $query)) {
    header("Location: admin_crud.php");
    exit();
} else {
    echo "Error: " . mysqli_error($connect);
}
?>