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
<!-- ... rest of the HTML remains the same, just update the title ... -->

<!-- ... rest of the code ... -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gambar Bus</title>
    <style>
        .drop-zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #1976d2;
            background-color: #f0f8ff;
        }
        .drop-zone i {
            font-size: 48px;
            color: #ccc;
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
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
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
            background: #1976d2;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div>
        <h1>Kelola Gambar Bus - <?php echo $bus['nama_perusahaan'] ?> <?php echo $bus['tipe bus'] ?></h1>
        <a href="admin_bus.php">Kembali ke Data Bus</a>
        
        <!-- Drag & Drop Zone -->
        <div class="drop-zone" id="dropZone">
            <i class="fas fa-cloud-upload-alt"></i>
            <h3>Drag & Drop Gambar di sini</h3>
            <p>atau klik untuk memilih file</p>
            <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
            <div class="progress-bar" id="progressBar">
                <div class="progress" id="progress"></div>
            </div>
        </div>

        <!-- Daftar Gambar -->
        <div>
            <h3>Daftar Gambar</h3>
            <div class="gallery" id="gallery">
                <?php if (mysqli_num_rows($result_gambar) > 0): ?>
                    <?php while($gambar = mysqli_fetch_array($result_gambar)): ?>
                        <div class="gallery-item">
                            <img src="uploads/<?php echo $gambar['gambar_url'] ?>" alt="Gambar Bus">
                            <button class="delete-btn" onclick="deleteImage(<?php echo $gambar['id'] ?>)">×</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Belum ada gambar untuk bus ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

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