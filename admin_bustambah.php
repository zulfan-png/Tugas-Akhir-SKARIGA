[file name]: admin_bustambah.php
[file content begin]
<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Bus</title>
</head>
<body>
    <h1>Tambah Data Bus</h1>
    <form action="admin_busproses_tambah.php" method="post">
        <div>
            <label>Perusahaan:</label>
            <input type="text" name="perusahaan" required>
        </div>
        
        <div>
            <label>Tipe Bus:</label>
            <input type="text" name="tipe_bus" required>
        </div>
        
        <div>
            <label>Jenis:</label>
            <input type="text" name="jenis" required>
        </div>
        
        <div>
            <label>Kapasitas:</label>
            <input type="number" name="kapasitas" required>
        </div>

        <div>
            <label>Status:</label>
            <select name="status" required>
                <option value="Tersedia" selected>Tersedia</option>
                <option value="Dipesan">Dipesan</option>
            </select>
        </div>

        <div>
            <label>Fasilitas:</label><br>
            <input type="checkbox" name="fasilitas[]" value="AC"> AC<br>
            <input type="checkbox" name="fasilitas[]" value="Toilet"> Toilet<br>
            <input type="checkbox" name="fasilitas[]" value="TV"> TV<br>
            <input type="checkbox" name="fasilitas[]" value="Karaoke"> Karaoke<br>
            <input type="checkbox" name="fasilitas[]" value="WiFi"> WiFi<br>
            <input type="checkbox" name="fasilitas[]" value="Charging Port"> Charging Port<br>
            <input type="checkbox" name="fasilitas[]" value="Reclining Seat"> Reclining Seat<br>
            <input type="checkbox" name="fasilitas[]" value="Bantal Selimut"> Bantal Selimut<br>
            <input type="checkbox" name="fasilitas[]" value="Bagasi Luas"> Bagasi Luas<br>
            <input type="checkbox" name="fasilitas[]" value="Snack"> Snack<br>
        </div>

        <div>
            <label>Deskripsi:</label>
            <textarea name="deskripsi" required></textarea>
        </div>

        <div>
            <h3>Harga Weekday</h3>
            <div>
                <label>Harga 6 jam:</label>
                <input type="number" name="harga1" required>
            </div>
            
            <div>
                <label>Harga 12 jam:</label>
                <input type="number" name="harga2" required>
            </div>
            
            <div>
                <label>Harga 24 jam:</label>
                <input type="number" name="harga3" required>
            </div>
        </div>

        <div>
            <h3>Harga Weekend</h3>
            <div>
                <label>Harga 6 jam:</label>
                <input type="number" name="harga4" required>
            </div>
            
            <div>
                <label>Harga 12 jam:</label>
                <input type="number" name="harga5" required>
            </div>
            
            <div>
                <label>Harga 24 jam:</label>
                <input type="number" name="harga6" required>
            </div>
        </div>
        
        <div>
            <button type="submit">Simpan Data</button>
            <a href="admin_bus.php">Batal</a>
        </div>
    </form>
</body>
</html>
[file content end]