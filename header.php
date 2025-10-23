<?php
// header.php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>window.location.href = 'index.html'</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - BISATA</title>
    <!-- Bootstrap 3.4.1 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome 4.7.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    
    <style>
        .gambar-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .no-image {
            color: #999;
            font-style: italic;
            font-size: 12px;
        }
        .table-responsive {
            border: 1px solid #f4f4f4;
        }
        .btn-gambar {
            margin-top: 5px;
            font-size: 12px;
            padding: 3px 8px;
        }
        .content-header {
            position: relative;
            padding: 15px 15px 0 15px;
        }
        .content {
            min-height: 500px;
        }
    </style>
</head>
<body class="skin-blue sidebar-mini">
    <div class="wrapper">
        <!-- Header -->
        <header class="main-header">
            <a href="index.php" class="logo">
                <span class="logo-mini"><b>B</b>ST</span>
                <span class="logo-lg"><b>BISATA</b> Admin</span>
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <a href="?logout=true">
                                <i class="fa fa-sign-out"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>