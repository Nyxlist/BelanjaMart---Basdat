<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . "/../config/config.php";

// 🔒 redirect kalau belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/role.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];

// ambil semua produk
$products = mysqli_query($conn, "SELECT * FROM products");

if (!$products) {
    die("Query products error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>BelanjaMart</title>
</head>
<body>

<!-- 🔥 INFO USER -->
<div style="background:#eee; padding:10px;">
    Login sebagai: <b><?php echo htmlspecialchars($role); ?></b> | 
    Nama: <b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b> |
    <a href="../auth/logout.php">Logout</a>
</div>

<h1>Daftar Produk</h1>

<?php while ($p = mysqli_fetch_assoc($products)) { ?>
    <div style="border:1px solid #000; margin:10px; padding:10px;">
        
        <h3><?php echo htmlspecialchars($p['product_name']); ?></h3>
        <p>Harga: Rp <?php echo number_format($p['price']); ?></p>

        <?php
        // ⭐ ambil rating
        $rating = mysqli_query($conn, "
            SELECT 
                ROUND(AVG(r.rating),1) as avg_rating,
                COUNT(*) as total
            FROM reviews r
            JOIN order_items oi ON r.order_item_id = oi.order_item_id
            WHERE oi.product_id = " . (int)$p['product_id']
        );

        if (!$rating) {
            die("Query rating error: " . mysqli_error($conn));
        }

        $r = mysqli_fetch_assoc($rating);
        ?>

        <p>
            Rating Produk: ⭐ <?php echo $r['avg_rating'] ?? 0; ?> / 5 
            (<?php echo $r['total'] ?? 0; ?> review)
        </p>

        <?php
        // 🔍 cek apakah user pernah beli (dan delivered)
        $orderCheck = mysqli_query($conn, "
            SELECT oi.order_item_id
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id = " . (int)$p['product_id'] . "
            AND o.user_id = $user_id
            AND o.status = 'delivered'
            LIMIT 1
        ");

        if (!$orderCheck) {
            die("Query orderCheck error: " . mysqli_error($conn));
        }

        $orderData = mysqli_fetch_assoc($orderCheck);
        ?>

        <!-- tombol review hanya untuk buyer -->
        <?php if ($role == 'buyer' && $orderData) { ?>
            <a href="product.php?order_item_id=<?php echo $orderData['order_item_id']; ?>">
                Tulis Review
            </a>
        <?php } ?>

        <h4>Review:</h4>

        <?php
        $reviews = mysqli_query($conn, "
            SELECT r.rating, r.comment, r.created_at, u.name AS username
            FROM reviews r
            JOIN order_items oi ON r.order_item_id = oi.order_item_id
            JOIN orders o ON oi.order_id = o.order_id
            JOIN users u ON o.user_id = u.user_id
            WHERE oi.product_id = " . (int)$p['product_id'] . "
            ORDER BY r.created_at DESC
        ");

        if (!$reviews) {
            die("Query reviews error: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($reviews) == 0) {
            echo "<i>Belum ada review</i>";
        } else {
            while ($rev = mysqli_fetch_assoc($reviews)) {
        ?>

        <div style="border:1px solid #ccc; margin:5px; padding:5px;">
            <b><?php echo htmlspecialchars($rev['username']); ?></b>
            <span style="color:green;">✔ Verified</span><br>

            ⭐ <?php echo $rev['rating']; ?>/5<br>
            <?php echo htmlspecialchars($rev['comment']); ?><br>

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