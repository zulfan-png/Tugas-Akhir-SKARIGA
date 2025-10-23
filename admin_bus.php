<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Bus - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #007bff;
        }
        .btn-tambah {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-tambah:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        .aksi {
            display: flex;
            gap: 10px;
        }
        .btn-edit {
            background: #28a745;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-hapus {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-gambar {
            background: #17a2b8;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            display: block;
            margin-top: 5px;
            text-align: center;
        }
        .btn-edit:hover { background: #218838; }
        .btn-hapus:hover { background: #c82333; }
        .btn-gambar:hover { background: #138496; }
        .gambar-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .no-image {
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Data Bus</h1>
            <a href="admin_bustambah.php" class="btn-tambah">Tambah Data Bus</a>
        </div>
        
        <div>
            <table>
                <thead>
                    <tr>    
                        <th>Perusahaan</th>
                        <th>Tipe Bus</th>
                        <th>Jenis</th>
                        <th>Kapasitas</th>
                        <th>fasilitas</th>
                        <th>Deskripsi</th>
                        <th>Gambar</th>
                        <th>Harga 6 jam (Weekday)</th>
                        <th>Harga 12 jam (Weekday)</th>
                        <th>Harga 24 jam (Weekday)</th>
                        <th>Harga 6 jam (Weekend)</th>
                        <th>Harga 12 jam (Weekend)</th>
                        <th>Harga 24 jam (Weekend)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT b.*, 
                             (SELECT gambar_url FROM bus_gambar WHERE bus_id = b.id LIMIT 1) as gambar_utama 
                             FROM bus b";
                    $result = mysqli_query($connect, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result)){
                    ?>
                        <tr>
                            <td><?php echo $row['perusahaan'] ?></td>
                            <td><?php echo $row['tipe bus'] ?></td>
                            <td><?php echo $row['jenis'] ?></td>
                            <td><?php echo $row['kapasitas'] ?></td>
                            <td><?php echo substr($row['fasilitas'], 0, 50) . '...' ?></td>
                            <td><?php echo substr($row['deskripsi'], 0, 50) . '...' ?></td>
                            <td>
                                <?php if(!empty($row['gambar_utama'])): ?>
                                    <img src="uploads/<?php echo $row['gambar_utama'] ?>" class="gambar-thumb">
                                <?php else: ?>
                                    <span class="no-image">No Image</span>
                                <?php endif; ?>
                                <a href="admin_bus_gambar.php?id=<?php echo $row['id'] ?>" class="btn-gambar">
                                    Kelola Gambar
                                </a>
                            </td>
                            <td>Rp <?php echo number_format($row['harga1'], 0, ',', '.') ?></td>
                            <td>Rp <?php echo number_format($row['harga2'], 0, ',', '.') ?></td>
                            <td>Rp <?php echo number_format($row['harga3'], 0, ',', '.') ?></td>
                            <td>Rp <?php echo number_format($row['harga4'], 0, ',', '.') ?></td>
                            <td>Rp <?php echo number_format($row['harga5'], 0, ',', '.') ?></td>
                            <td>Rp <?php echo number_format($row['harga6'], 0, ',', '.') ?></td>
                            <td class="aksi">
                                <a href="admin_busedit.php?id=<?php echo $row['id'] ?>" class="btn-edit">Edit</a>
                                <a href="admin_busproses_hapus.php?id=<?php echo $row['id'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>    
                    <?php }
                    } else { ?>
                        <tr>
                            <td colspan="14" style="text-align: center; padding: 20px;">
                                Tidak ada data bus
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>