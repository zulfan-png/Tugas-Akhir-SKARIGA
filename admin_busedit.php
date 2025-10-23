<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "SELECT * FROM bus WHERE id = $id";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_array($result);

// Pisahkan fasilitas menjadi array
$fasilitas_array = [];
if(!empty($row['fasilitas'])) {
    $fasilitas_array = explode(", ", $row['fasilitas']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Bus</title>
</head>
<body>
    <h1>Edit Data Bus</h1>
    <form action="admin_busproses_edit.php" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
        
        <div>
            <label>Perusahaan:</label>
            <input type="text" name="perusahaan" value="<?php echo $row['perusahaan'] ?>" required>
        </div>
        
        <div>
            <label>Tipe Bus:</label>
            <input type="text" name="tipe_bus" value="<?php echo $row['tipe bus'] ?>" required>
        </div>
        
        <div>
            <label>Jenis:</label>
            <input type="text" name="jenis" value="<?php echo $row['jenis'] ?>" required>
        </div>
        
        <div>
            <label>Kapasitas:</label>
            <input type="number" name="kapasitas" value="<?php echo $row['kapasitas'] ?>" required>
        </div>

        <div>
            <label>Fasilitas:</label><br>
            <input type="checkbox" name="fasilitas[]" value="AC" <?php echo in_array("AC", $fasilitas_array) ? 'checked' : ''; ?>> AC<br>
            <input type="checkbox" name="fasilitas[]" value="Toilet" <?php echo in_array("Toilet", $fasilitas_array) ? 'checked' : ''; ?>> Toilet<br>
            <input type="checkbox" name="fasilitas[]" value="TV" <?php echo in_array("TV", $fasilitas_array) ? 'checked' : ''; ?>> TV<br>
            <input type="checkbox" name="fasilitas[]" value="Karaoke" <?php echo in_array("Karaoke", $fasilitas_array) ? 'checked' : ''; ?>> Karaoke<br>
            <input type="checkbox" name="fasilitas[]" value="WiFi" <?php echo in_array("WiFi", $fasilitas_array) ? 'checked' : ''; ?>> WiFi<br>
            <input type="checkbox" name="fasilitas[]" value="Charging Port" <?php echo in_array("Charging Port", $fasilitas_array) ? 'checked' : ''; ?>> Charging Port<br>
            <input type="checkbox" name="fasilitas[]" value="Reclining Seat" <?php echo in_array("Reclining Seat", $fasilitas_array) ? 'checked' : ''; ?>> Reclining Seat<br>
            <input type="checkbox" name="fasilitas[]" value="Bantal Selimut" <?php echo in_array("Bantal Selimut", $fasilitas_array) ? 'checked' : ''; ?>> Bantal Selimut<br>
            <input type="checkbox" name="fasilitas[]" value="Bagasi Luas" <?php echo in_array("Bagasi Luas", $fasilitas_array) ? 'checked' : ''; ?>> Bagasi Luas<br>
            <input type="checkbox" name="fasilitas[]" value="Snack" <?php echo in_array("Snack", $fasilitas_array) ? 'checked' : ''; ?>> Snack<br>
        </div>

        <div>
            <label>Deskripsi:</label>
            <textarea name="deskripsi" required><?php echo $row['deskripsi'] ?></textarea>
        </div>

        <div>
            <h3>Harga Weekday</h3>
            <div>
                <label>Harga 6 jam:</label>
                <input type="number" name="harga1" value="<?php echo $row['harga1'] ?>" required>
            </div>
            
            <div>
                <label>Harga 12 jam:</label>
                <input type="number" name="harga2" value="<?php echo $row['harga2'] ?>" required>
            </div>
            
            <div>
                <label>Harga 24 jam:</label>
                <input type="number" name="harga3" value="<?php echo $row['harga3'] ?>" required>
            </div>
        </div>

        <div>
            <h3>Harga Weekend</h3>
            <div>
                <label>Harga 6 jam:</label>
                <input type="number" name="harga4" value="<?php echo $row['harga4'] ?>" required>
            </div>
            
            <div>
                <label>Harga 12 jam:</label>
                <input type="number" name="harga5" value="<?php echo $row['harga5'] ?>" required>
            </div>
            
            <div>
                <label>Harga 24 jam:</label>
                <input type="number" name="harga6" value="<?php echo $row['harga6'] ?>" required>
            </div>
        </div>
        
        <div>
            <button type="submit">Update Data</button>
            <a href="admin_bus.php">Batal</a>
        </div>
    </form>
</body>
</html>