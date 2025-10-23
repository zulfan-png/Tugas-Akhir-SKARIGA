<?php
    include 'koneksi.php';

    $user = $_POST['Username'];
    $pass = $_POST['Password'];


    $query = "SELECT * FROM datauser WHERE user='$user' and password='$pass'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row['level'] == 1) {
        header('Location: user_home.php');
    } else if ($row['level'] == 2) {
        header('Location: admin_bus.php');
    } else {
        echo "<script>
        alert('Anda gagal login');
        window.location.href = 'index.html';
        </script>";
    }
?>