<?php
include 'config.php';

$user_id = 1; // sementara (belum login)
$order_item_id = $_GET['order_item_id'] ?? 0;

// 🔒 Ambil data order + produk
$cek = mysqli_query($conn, "
SELECT oi.order_item_id, oi.product_id, p.product_name, p.price, o.status
FROM order_items oi
JOIN orders o ON oi.order_id = o.order_id
JOIN products p ON oi.product_id = p.product_id
WHERE oi.order_item_id = $order_item_id
AND o.user_id = $user_id
");

$data = mysqli_fetch_assoc($cek);

if (!$data) {
    die("❌ Data tidak valid");
}

if ($data['status'] != 'delivered') {
    die("❌ Belum bisa review (pesanan belum delivered)");
}

// 🔒 cek duplicate
$cek2 = mysqli_query($conn, "
SELECT * FROM reviews WHERE order_item_id = $order_item_id
");

$alreadyReviewed = mysqli_num_rows($cek2) > 0;

// ambil semua review produk ini
$reviews = mysqli_query($conn, "
SELECT r.rating, r.comment, r.created_at, u.username
FROM reviews r
JOIN order_items oi ON r.order_item_id = oi.order_item_id
JOIN users u ON oi.user_id = u.user_id
WHERE oi.product_id = {$data['product_id']}
ORDER BY r.created_at DESC
");

// hitung rating
$rating = mysqli_query($conn, "
SELECT ROUND(AVG(r.rating),1) as avg_rating, COUNT(*) as total
FROM reviews r
JOIN order_items oi ON r.order_item_id = oi.order_item_id
WHERE oi.product_id = {$data['product_id']}
");

$r = mysqli_fetch_assoc($rating);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1><?= $data['product_name'] ?></h1>
    <p class="price">Rp <?= number_format($data['price']) ?></p>

    <p class="rating">
        ⭐ <?= $r['avg_rating'] ?? 0 ?> / 5 
        (<?= $r['total'] ?> review)
    </p>

    <hr>

    <h2>Review Produk</h2>

    <?php if (mysqli_num_rows($reviews) == 0) { ?>
        <p class="empty">Belum ada review</p>
    <?php } else { 
        while ($rev = mysqli_fetch_assoc($reviews)) { ?>
            <div class="review-box">
                <b><?= $rev['username'] ?></b>
                <span class="verified">✔ Verified</span><br>
                ⭐ <?= $rev['rating'] ?>/5<br>
                <p><?= $rev['comment'] ?></p>
                <small><?= $rev['created_at'] ?></small>
            </div>
    <?php } } ?>

    <hr>

    <h2>Tulis Review</h2>

    <?php if ($alreadyReviewed) { ?>
        <p class="warning">Kamu sudah review produk ini</p>
    <?php } else { ?>

    <form method="POST" class="form-review">
        <label>Rating:</label><br>
        <select name="rating">
            <option value="5">⭐⭐⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="2">⭐⭐</option>
            <option value="1">⭐</option>
        </select><br><br>

        <label>Komentar:</label><br>
        <textarea name="comment" required></textarea><br><br>

        <button name="submit">Kirim Review</button>
    </form>

    <?php } ?>

    <br>
    <a href="index.php">⬅ Kembali</a>

</div>

<?php
if (isset($_POST['submit']) && !$alreadyReviewed) {

    $rating = (int) $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn, "
    INSERT INTO reviews (order_item_id, rating, comment)
    VALUES ($order_item_id, $rating, '$comment')
    ");

    header("Location: product.php?order_item_id=$order_item_id");
}
?>

</body>
</html>