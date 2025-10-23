<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>
</head>
<body>
    <a href="admin_user_tambah.php">Tambah</a>
    <table border="1">
        <tr>
            <th>id_user</th>
            <th>Nama</th>
            <th>Password</th>
            <th>Email</th>
            <th>Nomor HP</th>
            <th>Level</th>
            <th>fungsi</th>
        </tr>

        <?php
        $query = "SELECT * FROM datauser";
        $result = mysqli_query($connect, $query);

        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)){
        ?>
            <tr>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo $row['user'] ?></td>
                <td><?php echo $row['password'] ?></td>
                <td><?php echo $row['email'] ?></td>
                <td><?php echo $row['nomor hp'] ?></td>
                <td>
                    <?php 
                    if ($row['level'] == 1) {
                        echo 'User';
                    } elseif ($row['level'] == 2) {
                        echo 'Admin';
                    } else {
                        echo $row['level'];
                    }
                    ?>
                </td>
                <td>
                    <a href="admin_user_edit.php?id=<?php echo $row['id'] ?>">Edit</a>
                    <a href="admin_user_hapus.php?id=<?php echo $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>    
        <?php }
        } else {
            echo "<tr><td colspan='7'>Tidak ada data</td></tr>";
        }
        ?>
    </table>
</body>
</html>