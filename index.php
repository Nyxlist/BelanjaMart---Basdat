<?php
include 'config.php';

$products = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
    <title>BelanjaMart</title>
</head>
<body>

<h1>Daftar Produk</h1>

<?php while ($p = mysqli_fetch_assoc($products)) { ?>
    <div style="border:1px solid #000; margin:10px; padding:10px;">
        
        <h3><?php echo $p['product_name']; ?></h3>
        <p>Harga: Rp <?php echo number_format($p['price']); ?></p>

        <?php
        // ⭐ ambil rating REAL dari tabel reviews
        $rating = mysqli_query($conn, "
            SELECT ROUND(AVG(rating),1) as avg_rating, COUNT(*) as total
            FROM reviews
            WHERE product_id = " . $p['product_id']
        );
        $r = mysqli_fetch_assoc($rating);
        ?>

        <p>
            Rating Produk: ⭐ <?php echo $r['avg_rating'] ?? 0; ?> / 5 
            (<?php echo $r['total']; ?> review)
        </p>

        <!-- tombol review -->
        <a href="product.php?order_item_id=<?= $order_item_id ?>">
            Tulis Review
        </a>

        <h4>Review:</h4>

        <?php
        // ambil review + username
        $reviews = mysqli_query($conn, "
            SELECT r.rating, r.comment, r.created_at, u.username
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.product_id = " . $p['product_id'] . "
            ORDER BY r.created_at DESC
        ");

        if (mysqli_num_rows($reviews) == 0) {
            echo "<i>Belum ada review</i>";
        } else {
            while ($rev = mysqli_fetch_assoc($reviews)) {
        ?>

        <div style="border:1px solid #ccc; margin:5px; padding:5px;">
            <b><?php echo $rev['username']; ?></b>
            <span style="color:green;">✔ Verified</span><br>

            ⭐ <?php echo $rev['rating']; ?>/5<br>
            <?php echo $rev['comment']; ?><br>

            <small><?php echo $rev['created_at']; ?></small>
        </div>

        <?php 
            }
        }
        ?>

    </div>
<?php } ?>

</body>
</html>