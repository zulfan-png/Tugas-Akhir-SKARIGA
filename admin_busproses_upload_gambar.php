<?php
include "koneksi.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bus_id = $_POST['bus_id'];
    
    // Cek apakah folder uploads ada
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $uploaded_files = [];
    $errors = [];
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['images']['name'][$key];
        $file_size = $_FILES['images']['size'][$key];
        $file_tmp = $_FILES['images']['tmp_name'][$key];
        $file_type = $_FILES['images']['type'][$key];
        
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "File $file_name bukan gambar yang valid";
            continue;
        }
        
        if ($file_size > $max_size) {
            $errors[] = "File $file_name terlalu besar (maksimal 5MB)";
            continue;
        }
        
        // Generate unique filename
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
        $upload_path = 'uploads/' . $new_filename;
        
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Simpan ke database
            $query = "INSERT INTO bus_gambar (bus_id, gambar_url) VALUES ('$bus_id', '$new_filename')";
            if (mysqli_query($connect, $query)) {
                $uploaded_files[] = $new_filename;
            } else {
                $errors[] = "Gagal menyimpan $file_name ke database";
                unlink($upload_path); // Hapus file yang sudah diupload
            }
        } else {
            $errors[] = "Gagal upload $file_name";
        }
    }
    
    if (empty($errors)) {
        echo json_encode([
            'success' => true,
            'message' => 'Semua gambar berhasil diupload',
            'files' => $uploaded_files
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors),
            'files' => $uploaded_files
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
}
?>