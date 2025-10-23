<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
</head>
<body>
    <h2>Tambah User</h2>
    <form action="admin_user_proses_tambah.php" method="post">
        <table>
            <tr>
                <td>Nama</td>
                <td><input type="text" name="user" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" required></td>
            </tr>
            <tr>
                <td>Nomor HP</td>
                <td><input type="text" name="nomor_hp" required></td>
            </tr>
            <tr>
                <td>Level</td>
                <td>
                    <select name="level" required>
                        <option value="1">User</option>
                        <option value="2">Admin</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="submit">Simpan</button>
                    <a href="admin_user.php">Batal</a>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>