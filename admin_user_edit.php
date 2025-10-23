<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "SELECT * FROM datauser WHERE id = $id";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_array($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <form action="admin_user_proses_edit.php" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
        <table>
            <tr>
                <td>Nama</td>
                <td><input type="text" name="user" value="<?php echo $row['user'] ?>" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" placeholder="Kosongkan jika tidak diubah"></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" value="<?php echo $row['email'] ?>" required></td>
            </tr>
            <tr>
                <td>Nomor HP</td>
                <td><input type="text" name="nomor_hp" value="<?php echo $row['nomor hp'] ?>" required></td>
            </tr>
            <tr>
                <td>Level</td>
                <td>
                    <select name="level" required>
                        <option value="1" <?php echo $row['level'] == 1 ? 'selected' : ''; ?>>User</option>
                        <option value="2" <?php echo $row['level'] == 2 ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <button type="submit">Update</button>
                    <a href="admin_user.php">Batal</a>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>