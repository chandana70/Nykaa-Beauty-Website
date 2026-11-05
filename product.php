
<?php
session_start();
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found. <a href='products.php'>Go back</a>");
}

$message = "";

// ADD TO CART
if (isset($_POST['add_to_cart'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    $check = $conn->prepare(
        "SELECT stock FROM products WHERE product_id=?"
    );
    $check->bind_param("i", $id);
    $check->execute();
    $stockResult = $check->get_result()->fetch_assoc();

    if (!$stockResult || $stockResult['stock'] < $quantity) {
        $message = "Sorry, requested quantity is not available.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO cart_items (user_id, product_id, quantity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
        );

        $stmt->bind_param("iii", $user_id, $id, $quantity);

        if ($stmt->execute()) {
            header("Location: cart.php");
            exit();
        }
    }
}

// STOCK ALERT REQUEST
if (isset($_POST['stock_alert'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $check = $conn->prepare(
        "SELECT alert_id FROM stock_alerts
         WHERE user_id=? AND product_id=?"
    );

    $check->bind_param("ii", $user_id, $id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $message = "You have already requested a stock alert!";
    } else {

        $insert = $conn->prepare(
            "INSERT INTO stock_alerts (user_id, product_id)
             VALUES (?, ?)"
        );

        $insert->bind_param("ii", $user_id, $id);

        if ($insert->execute()) {
            $message = "Stock alert request saved successfully!";
        }
    }
}
if (isset($_POST['add_wishlist'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if ($product['stock'] <= 0) {
        $message = "This product is currently out of stock.";
    } else {
        $user_id = $_SESSION['user_id'];

        $check = $conn->prepare(
            "SELECT wishlist_id FROM wishlist WHERE user_id=? AND product_id=?"
        );
        $check->bind_param("ii", $user_id, $id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $message = "Product already exists in your wishlist!";
        } else {
            $insert = $conn->prepare(
                "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)"
            );
            $insert->bind_param("ii", $user_id, $id);

            if ($insert->execute()) {
                $message = "Product added to your wishlist!";
            } else {
                $message = "Unable to add product.";
            }
        }
    }
}
?>
<?php
$ratingMessage = "";

// Submit or update a review
if (isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $review = trim($_POST['review']);

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare(
            "INSERT INTO reviews (user_id, product_id, rating, review)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             rating = VALUES(rating), review = VALUES(review)"
        );

        $stmt->bind_param("iiis", $user_id, $id, $rating, $review);

        if ($stmt->execute()) {
            $ratingMessage = "Thank you for your review!";
        }
    }
}

// Get average rating
$stmt = $conn->prepare(
    "SELECT AVG(rating) AS average_rating, COUNT(*) AS total_reviews
     FROM reviews WHERE product_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$ratingData = $stmt->get_result()->fetch_assoc();

// Get customer reviews
$stmt = $conn->prepare(
    "SELECT reviews.rating, reviews.review, users.name
     FROM reviews
     JOIN users ON reviews.user_id = users.user_id
     WHERE reviews.product_id = ?
     ORDER BY reviews.created_at DESC"
);
$stmt->bind_param("i", $id);
// NOTIFY ME WHEN AVAILABLE
if (isset($_POST['notify_me'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO stock_alerts (user_id, product_id)
         VALUES (?, ?)"
    );

    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();

    $message = "You will be notified when this product is available!";
}
$stmt->execute();
$allReviews = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details | Nykaa</title>
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
        </div>
    </div>
</header>

<section class="product-details">

    <div class="detail-image">
       <img src="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>"
     alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>

    <div class="detail-info">

        <p class="brand"><?php echo htmlspecialchars($product['brand']); ?></p>

        <h2><?php echo htmlspecialchars($product['name']); ?></h2>

        <h3>₹<?php echo number_format($product['price'], 2); ?></h3>

        <p><?php echo htmlspecialchars($product['description']); ?></p>

        <p class="stock">
            <?php
            echo $product['stock'] > 0
                ? "In Stock (" . $product['stock'] . " available)"
                : "Out of Stock";
            ?>
        </p>

        <?php if ($message != "") { ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>
<div class="product-reviews">
    <h2>Customer Ratings & Reviews</h2>

    <h3>
        ⭐
        <?php echo $ratingData['total_reviews'] > 0
            ? number_format($ratingData['average_rating'], 1)
            : "No ratings yet"; ?>
        / 5
    </h3>

    <p><?php echo $ratingData['total_reviews']; ?> customer reviews</p>

    <?php if (isset($_SESSION['user_id'])) { ?>
        <h3>Write a Review</h3>

        <form method="POST">
            <label>Your Rating:</label>
            <select name="rating" required>
                <option value="5">★★★★★ - Excellent</option>
                <option value="4">★★★★ - Very Good</option>
                <option value="3">★★★ - Good</option>
                <option value="2">★★ - Average</option>
                <option value="1">★ - Poor</option>
            </select>

            <textarea name="review"
                placeholder="Share your experience..."
                required></textarea>

            <button type="submit" name="submit_review">
                Submit Review
            </button>
        </form>

        <p><?php echo htmlspecialchars($ratingMessage); ?></p>
    <?php } else { ?>
        <p><a href="login.php">Login</a> to write a review.</p>
    <?php } ?>

    <h3>What Customers Say</h3>

    <?php while ($review = $allReviews->fetch_assoc()) { ?>
        <div class="review-card">
            <b><?php echo htmlspecialchars($review['name']); ?></b>

            <p>
                <span class="stars">
                    <?php echo str_repeat("★", $review['rating']); ?>
                    <?php echo str_repeat("☆", 5 - $review['rating']); ?>
                </span>
            </p>

            <p><?php echo htmlspecialchars($review['review']); ?></p>
        </div>
    <?php } ?>
</div>
        <form method="POST">
            <button type="submit" name="add_wishlist"
                class="shop-btn">
                ♡ ADD TO WISHLIST
            </button>
        </form>
        
<form method="POST">
    <label>Quantity:</label>
    <input type="number" name="quantity"
           value="1" min="1"
           max="<?php echo (int)$product['stock']; ?>"
           required>

    <button type="submit" name="add_to_cart" class="shop-btn">

    <?php if ($product['stock'] <= 0) { ?>
    <form method="POST">
        <button type="submit" name="notify_me">
            🔔 NOTIFY ME WHEN AVAILABLE
        </button>
    </form>
<?php } ?>
    </button>
</form>

        <?php if ($product['stock'] <= 0) { ?>

    <form method="POST">
        <button type="submit" name="stock_alert" class="shop-btn">
            NOTIFY ME WHEN AVAILABLE
        </button>
    </form>

<?php } ?>

        <br>
        <a href="products.php">← Continue Shopping</a>

    </div>

</section>

<footer>
    <h2>nykaa.</h2>
    <p>Beauty is for everyone.</p>
</footer>

</body>
</html>
