<?php
include 'koneksi.php';

$bus_id = $_GET['id'];

// Ambil data bus dengan join perusahaan
$query_bus = "SELECT b.*, p.nama_perusahaan 
              FROM bus b 
              LEFT JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
              WHERE b.id = $bus_id";
$result_bus = mysqli_query($connect, $query_bus);
$bus = mysqli_fetch_array($result_bus);

// Ambil gambar bus
$query_gambar = "SELECT * FROM bus_gambar WHERE bus_id = $bus_id";
$result_gambar = mysqli_query($connect, $query_gambar);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gambar Bus - BISATA</title>
    <!-- Bootstrap 3.4.1 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome 4.7.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    
    <style>
        .drop-zone {
            border: 2px dashed #d2d6de;
            border-radius: 5px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #3c8dbc;
            background-color: #ecf0f5;
        }
        .drop-zone i {
            font-size: 48px;
            color: #d2d6de;
            margin-bottom: 10px;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .gallery-item {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            background: white;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        .gallery-item img {
            width: 150px;
            height: 100px;
            object-fit: cover;
        }
        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dd4b39;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
            display: none;
        }
        .progress {
            height: 100%;
            background: #3c8dbc;
            width: 0%;
            transition: width 0.3s;
        }
        .box-header {
            border-bottom: 1px solid #f4f4f4;
        }
        .box-title {
            font-size: 18px;
            font-weight: 500;
        }
        .content-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content-header .breadcrumb {
            float: right;
            background: transparent;
            margin-top: 0;
            margin-bottom: 0;
            font-size: 12px;
            padding: 7px 5px;
            position: absolute;
            top: 15px;
            right: 10px;
            border-radius: 2px;
        }
    </style>
</head>
<body class="skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include 'header.php'; ?>
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    Kelola Gambar Bus
                    <small><?php echo $bus['nama_perusahaan'] ?> <?php echo $bus['tipe bus'] ?></small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="admin_bus.php"><i class="fa fa-bus"></i> Data Bus</a></li>
                    <li class="active">Kelola Gambar</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Drag & Drop Box -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Upload Gambar</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="drop-zone" id="dropZone">
                                    <i class="fa fa-cloud-upload"></i>
                                    <h3>Drag & Drop Gambar di sini</h3>
                                    <p>atau klik untuk memilih file</p>
                                    <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                                    <div class="progress-bar" id="progressBar">
                                        <div class="progress" id="progress"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Box -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Daftar Gambar</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="gallery" id="gallery">
                                    <?php if (mysqli_num_rows($result_gambar) > 0): ?>
                                        <?php while($gambar = mysqli_fetch_array($result_gambar)): ?>
                                            <div class="gallery-item">
                                                <img src="uploads/<?php echo $gambar['gambar_url'] ?>" alt="Gambar Bus">
                                                <button class="delete-btn" onclick="deleteImage(<?php echo $gambar['id'] ?>)">×</button>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> Belum ada gambar untuk bus ini.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php include 'footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery 3.4.1 -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <!-- Bootstrap 3.4.1 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js"></script>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const progressBar = document.getElementById('progressBar');
        const progress = document.getElementById('progress');
        const gallery = document.getElementById('gallery');
        const busId = <?php echo $bus_id; ?>;

        // Click to select files
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag and drop events
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                uploadFiles(files);
            }
        }

        function uploadFiles(files) {
            progressBar.style.display = 'block';
            
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }
            formData.append('bus_id', busId);

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progress.style.width = percentComplete + '%';
                }
            });

            xhr.addEventListener('load', (e) => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Gambar berhasil diupload!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                } else {
                    alert('Upload gagal!');
                }
                progressBar.style.display = 'none';
                progress.style.width = '0%';
            });

            xhr.addEventListener('error', () => {
                alert('Upload error!');
                progressBar.style.display = 'none';
                progress.style.width = '0%';
            });

            xhr.open('POST', 'admin_busproses_upload_gambar.php');
            xhr.send(formData);
        }

        function deleteImage(imageId) {
            if (confirm('Yakin ingin menghapus gambar ini?')) {
                const formData = new FormData();
                formData.append('image_id', imageId);

                const xhr = new XMLHttpRequest();
                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('Gambar berhasil dihapus!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } else {
                        alert('Hapus gagal!');
                    }
                });

                xhr.open('POST', 'admin_busproses_hapus_gambar.php');
                xhr.send(formData);
            }
        }
    </script>
</body>
</html>