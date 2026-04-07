<?php
include 'config.php';

$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>BelanjaMart</title>
</head>
<body>

<h1>Daftar Produk</h1>

<?php if (mysqli_num_rows($result) == 0) { ?>
    <p>Belum ada produk</p>
<?php } ?>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
    <div style="border:1px solid #000; margin:10px; padding:10px;">
        <h3><?php echo $row['product_name']; ?></h3>
        <p>Harga: Rp <?php echo $row['price']; ?></p>
        <p>Rating: <?php echo $row['average_rating']; ?></p>
    </div>
<?php } ?>

</body>
</html>