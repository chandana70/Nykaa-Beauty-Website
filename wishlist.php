<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Remove product from wishlist
if (isset($_POST['remove'])) {
    $product_id = (int)$_POST['product_id'];

    $stmt = $conn->prepare(
        "DELETE FROM wishlist WHERE user_id=? AND product_id=?"
    );
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    $message = "Product removed from wishlist.";
}

// Display wishlist products
$stmt = $conn->prepare(
    "SELECT p.* FROM products p
     INNER JOIN wishlist w ON p.product_id = w.product_id
     WHERE w.user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Wishlist | Nykaa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <div class="topbar">
        <h1>nykaa<span>.</span></h1>
        <div class="header-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="wishlist.php">♡ Wishlist</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>

<div class="container">
    <h2>My Wishlist ♡</h2>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <div class="product-grid">

    <?php if ($products->num_rows > 0) { ?>

        <?php while ($p = $products->fetch_assoc()) { ?>

            <div class="product-card">

                <div class="product-image">
                    <img src="https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?w=400"
                         alt="Beauty product">
                </div>

                <p class="brand">
                    <?php echo htmlspecialchars($p['brand']); ?>
                </p>

                <h3>
                    <?php echo htmlspecialchars($p['name']); ?>
                </h3>

                <h3>₹<?php echo $p['price']; ?></h3>

                <form method="POST">
                    <input type="hidden" name="product_id"
                           value="<?php echo $p['product_id']; ?>">

                    <button type="submit" name="remove"
                            class="shop-btn">
                        REMOVE
                    </button>
                </form>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>Your wishlist is empty. Explore our products!</p>

    <?php } ?>

    </div>

    <br>
    <a href="products.php">Continue Shopping</a>
</div>

<footer>
    <h2>nykaa.</h2>
    <p>Beauty is for everyone.</p>
</footer>

</body>
</html>