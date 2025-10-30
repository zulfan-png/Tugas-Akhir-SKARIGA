<?php
session_start();
include 'koneksi.php';

// Ambil data dari form
$username = $_POST['username'];
$password = $_POST['password'];

// Query untuk mencari user
$query = "SELECT * FROM datauser WHERE username = '$username' AND password = '$password'";
$result = mysqli_query($connect, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    // Set session variables
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
    $_SESSION['level'] = $row['level'];
    $_SESSION['login_time'] = time();
    
    // Redirect berdasarkan level
    switch ($row['level']) {
        case 'customer':
            header('Location: user_home.php');
            break;
        case 'admin':
            header('Location: admin_bus.php');
            break;
        case 'operator':
            header('Location: operator_dashboard.php');
            break;
        case 'supir':
            header('Location: supir_dashboard.php');
            break;
        default:
            // Default redirect untuk level yang tidak dikenali
            header('Location: user_home.php');
            break;
    }
    exit;
} else {
    echo "<script>
    alert('Username atau password salah!');
    window.location.href = 'index.html';
    </script>";
}
?>