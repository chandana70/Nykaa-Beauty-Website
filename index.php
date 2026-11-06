
<?php
session_start();

$lastVisit = "";

if (isset($_COOKIE['last_visit'])) {
    $lastVisit = $_COOKIE['last_visit'];
}

setcookie("last_visit", date("d-m-Y h:i:s A"), time() + (86400 * 30), "/");

include 'db.php';

$categories = $conn->query("SELECT * FROM categories");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE name LIKE ? OR brand LIKE ?"
    );
    $term = "%$search%";
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT * FROM products LIMIT 8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nykaa - Beauty & You</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="topbar">
        <h1>nykaa<span></span></h1>

        <form action="index.php" method="GET" class="search">
            <input type="text" name="search"
                placeholder="Search for products, brands..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="header-links">
            <?php if (isset($_SESSION['user_id'])) { ?>
                <span>Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php">Logout</a>
            <?php } else { ?>
                <a href="login.php">Sign in</a>
                <a href="register.php">Register</a>
            <?php } ?>

            <a href="wishlist.php">♡ Wishlist</a>
        </div>
    </div>

    <nav>
        <a href="index.php">Home</a>

        <?php while($cat = $categories->fetch_assoc()) { ?>
            <a href="products.php?category=<?php echo $cat['category_id']; ?>">
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </a>
        <?php } ?>

        <a href="products.php">All Products</a>
        <a href="feedback.php">Feedback</a>
        <a href="interaction.php">JS Demo</a>
        <a href="manage_products.php">Manage Products</a>
        <a href="cart.php" class="nav-action">🛍 My Cart</a>
<a href="my_orders.php" class="nav-action">📦 My Orders</a>
    </nav>
    <a href="products.php">All Products</a>
<a href="cart.php">🛍 My Cart</a>
</header>


<?php if ($lastVisit != "") { ?>
    <p style="text-align:center; color:#e80071;">
        Welcome back! Your last visit was:
        <?php echo htmlspecialchars($lastVisit); ?>
    </p>
<?php } else { ?>
    <p style="text-align:center; color:#e80071;">
        Welcome to Nykaa!
    </p>
<?php } ?>
<section class="hero">
    <div>
        <p>BEAUTY • SKINCARE • MAKEUP</p>
        <h2>Discover Your<br>Beauty Essentials</h2>
        <p>Everything you need to feel beautiful and confident.</p>
        <a href="products.php" class="shop-btn">SHOP NOW</a>
    </div>
</section>

<section class="categories">
    <h2>Explore Categories</h2>
    <div class="category-grid">
        <a href="products.php?category=1">Skincare</a>
        <a href="products.php?category=2">Makeup</a>
        <a href="products.php?category=3">Haircare</a>
        <a href="products.php?category=4">Fragrance</a>
    </div>
</section>

<section class="products">
    <h2><?php echo $search ? "Search Results" : "Best Sellers"; ?></h2>

    <div class="product-grid">
        <?php if ($products && $products->num_rows > 0) { ?>
            <?php while($p = $products->fetch_assoc()) { ?>
                <div class="product-card">
                    <div class="product-image">
    <span class="heart">♡</span>
    <img src="<?php echo htmlspecialchars($p['image_url'] ?? ''); ?>"
     alt="<?php echo htmlspecialchars($p['name']); ?>">
</div>

                    <h3><?php echo htmlspecialchars($p['brand']); ?></h3>
                    <p><?php echo htmlspecialchars($p['name']); ?></p>
                    <?php
$ratingQuery = $conn->prepare(
    "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total
     FROM reviews WHERE product_id = ?"
);
$ratingQuery->bind_param("i", $p['product_id']);
$ratingQuery->execute();
$rating = $ratingQuery->get_result()->fetch_assoc();
?>

<p class="card-rating">
    ⭐ <?php echo $rating['total'] > 0
        ? number_format($rating['avg_rating'], 1) . " | " . $rating['total'] . " ratings"
        : "No ratings yet"; ?>
</p>
                    <strong>₹<?php echo number_format($p['price'], 2); ?></strong>

                    <p class="stock">
                        <?php
                        echo $p['stock'] > 0
                            ? "In Stock"
                            : "Out of Stock";
                        ?>
                    </p>

                    <a class="view-btn"
                       href="product.php?id=<?php echo $p['product_id']; ?>">
                       VIEW PRODUCT
                    </a>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No products found.</p>
        <?php } ?>
    </div>
</section>

<footer>
    <h2>nykaa.</h2>
    <p>Beauty is for everyone.</p>
    <p>© 2026 Nykaa Educational Replica</p>
</footer>

</body>
</html>
