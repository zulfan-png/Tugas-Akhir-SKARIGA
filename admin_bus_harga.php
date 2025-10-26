<?php
include 'koneksi.php';

$bus_id = $_GET['id'];

// Ambil data bus
$query_bus = "SELECT b.*, p.nama_perusahaan 
              FROM bus b 
              LEFT JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
              WHERE b.id = $bus_id";
$result_bus = mysqli_query($connect, $query_bus);
$bus = mysqli_fetch_array($result_bus);

// Ambil harga bus
$query_harga = "SELECT * FROM harga_bus WHERE bus_id = $bus_id";
$result_harga = mysqli_query($connect, $query_harga);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Harga Bus</title>
    <style>
        .harga-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .harga-table th, .harga-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .harga-table th { background-color: #f5f5f5; }
        .btn { padding: 5px 10px; margin: 2px; }
    </style>
</head>
<body>
    <div style="padding: 20px;">
        <h1>Kelola Harga Bus - <?php echo $bus['nama_perusahaan'] ?> <?php echo $bus['tipe bus'] ?></h1>
        <a href="admin_bus.php">Kembali ke Data Bus</a>
        
        <!-- Form Tambah Harga -->
        <h3>Tambah Harga Baru</h3>
        <form action="admin_busproses_tambah_harga.php" method="post" style="margin-bottom: 30px;">
            <input type="hidden" name="bus_id" value="<?php echo $bus_id ?>">
            <div style="display: flex; gap: 10px; align-items: end;">
                <div>
                    <label>Jenis Harga:</label>
                    <select name="jenis_harga" class="form-control" required>
                        <option value="Paket 6 Jam">Paket 6 Jam</option>
                        <option value="Paket 12 Jam">Paket 12 Jam</option>
                        <option value="Paket 24 Jam">Paket 24 Jam</option>
                        <option value="Paket 6 Jam (Weekend)">Paket 6 Jam (Weekend)</option>
                        <option value="Paket 12 Jam (Weekend)">Paket 12 Jam (Weekend)</option>
                        <option value="Paket 24 Jam (Weekend)">Paket 24 Jam (Weekend)</option>
                        <option value="Luar Kota">Luar Kota</option>
                    </select>
                </div>
                <div>
                    <label>Harga:</label>
                    <input type="number" name="harga" class="form-control" step="0.01" required>
                </div>
                <div>
                    <label>Satuan:</label>
                    <input type="text" name="satuan" class="form-control" value="Paket" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Tambah Harga</button>
                </div>
            </div>
        </form>

        <!-- Daftar Harga -->
        <h3>Daftar Harga</h3>
        <?php if (mysqli_num_rows($result_harga) > 0): ?>
            <table class="harga-table">
                <thead>
                    <tr>
                        <th>Jenis Harga</th>
                        <th>Harga</th>
                        <th>Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($harga = mysqli_fetch_array($result_harga)): ?>
                        <tr>
                            <td><?php echo $harga['jenis_harga'] ?></td>
                            <td>Rp <?php echo number_format($harga['harga'], 0, ',', '.') ?></td>
                            <td><?php echo $harga['satuan'] ?></td>
                            <td>
                                <a href="admin_busproses_hapus_harga.php?id=<?php echo $harga['id'] ?>&bus_id=<?php echo $bus_id ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin ingin menghapus harga ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Belum ada harga untuk bus ini.</p>
        <?php endif; ?>
    </div>
</body>
</html>