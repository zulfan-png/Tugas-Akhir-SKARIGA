<?php
include "koneksi.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $image_id = $_POST['image_id'];
    
    // Debug: cek apakah image_id diterima
    error_log("Image ID received: " . $image_id);
    
    // Ambil info gambar dari database
    $query_select = "SELECT * FROM bus_gambar WHERE id = '$image_id'";
    $result = mysqli_query($connect, $query_select);
    
    if (!$result) {
        error_log("Database error: " . mysqli_error($connect));
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . mysqli_error($connect)
        ]);
        exit;
    }
    
    $gambar = mysqli_fetch_assoc($result);
    
    if ($gambar) {
        // Hapus file dari server
        $file_path = 'uploads/' . $gambar['gambar_url'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                error_log("File deleted: " . $file_path);
            } else {
                error_log("Failed to delete file: " . $file_path);
            }
        } else {
            error_log("File not found: " . $file_path);
        }
        
        // Hapus dari database
        $query_delete = "DELETE FROM bus_gambar WHERE id = '$image_id'";
        if (mysqli_query($connect, $query_delete)) {
            error_log("Database record deleted successfully");
            echo json_encode([
                'success' => true,
                'message' => 'Gambar berhasil dihapus'
            ]);
        } else {
            error_log("Database delete error: " . mysqli_error($connect));
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menghapus dari database: ' . mysqli_error($connect)
            ]);
        }
    } else {
        error_log("Image not found in database with ID: " . $image_id);
        echo json_encode([
            'success' => false,
            'message' => 'Gambar tidak ditemukan di database'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
}
?>