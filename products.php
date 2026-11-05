
<?php
include 'db.php';

$category = $_GET['category'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'default';

$sql = "SELECT products.*, categories.category_name
        FROM products
        LEFT JOIN categories
        ON products.category_id = categories.category_id
        WHERE 1=1";

$params = [];
$types = "";

if ($category !== '' && ctype_digit((string)$category)) {
    $sql .= " AND products.category_id = ?";
    $params[] = (int)$category;
    $types .= "i";
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND products.price <= ?";
    $params[] = (float)$maxPrice;
    $types .= "d";
}

if ($sort === 'low') {
    $sql .= " ORDER BY products.price ASC";
} elseif ($sort === 'high') {
    $sql .= " ORDER BY products.price DESC";
} elseif ($sort === 'name') {
    $sql .= " ORDER BY products.name ASC";
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nykaa | All Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="topbar">
        <h1>nykaa<span>.</span></h1>
        <div class="header-links">
            <a href="index.php">Home</a>
            <a href="login.php">Sign in</a>
            <a href="wishlist.php">♡ Wishlist</a>
        </div>
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">All Products</a>
    </nav>
</header>

<section class="products">
    <h2>Explore Our Beauty Collection</h2>

    <form method="GET" class="filter-form">

        <select name="category">
            <option value="">All Categories</option>

            <?php while($cat = $categories->fetch_assoc()) { ?>
                <option value="<?php echo $cat['category_id']; ?>"
                    <?php echo ($category == $cat['category_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
            <?php } ?>
        </select>

        <select name="max_price">
            <option value="">Any Price</option>
            <option value="300" <?php if($maxPrice == '300') echo 'selected'; ?>>Below ₹300</option>
            <option value="500" <?php if($maxPrice == '500') echo 'selected'; ?>>Below ₹500</option>
            <option value="1000" <?php if($maxPrice == '1000') echo 'selected'; ?>>Below ₹1000</option>
        </select>

        <select name="sort">
            <option value="default">Sort By</option>
            <option value="low" <?php if($sort == 'low') echo 'selected'; ?>>Price: Low to High</option>
            <option value="high" <?php if($sort == 'high') echo 'selected'; ?>>Price: High to Low</option>
            <option value="name" <?php if($sort == 'name') echo 'selected'; ?>>Name: A to Z</option>
        </select>

        <button type="submit">Apply Filters</button>
        <a href="products.php">Reset</a>

    </form>

    <div class="product-grid">

        <?php if($products->num_rows > 0) { ?>

            <?php while($p = $products->fetch_assoc()) { ?>

                <div class="product-card">

                    <div class="product-image">
                       <img src="<?php echo htmlspecialchars($p['image_url'] ?? ''); ?>"
     alt="<?php echo htmlspecialchars($p['name']); ?>">
                    </div>

                    <h3><?php echo htmlspecialchars($p['brand']); ?></h3>

                    <p><?php echo htmlspecialchars($p['name']); ?></p>

                    <strong>₹<?php echo number_format($p['price'], 2); ?></strong>

                    <p class="stock">
                        <?php echo $p['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                    </p>

                    <a class="view-btn"
                       href="product.php?id=<?php echo $p['product_id']; ?>">
                       VIEW PRODUCT
                    </a>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>No products match your filters.</p>

        <?php } ?>

    </div>
</section>

<footer>
    <h2>nykaa.</h2>
    <p>Beauty is for everyone.</p>
    <p>© 2026 Educational Replica</p>
</footer>

</body>
</html>
