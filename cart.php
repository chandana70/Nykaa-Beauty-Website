
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// REMOVE ITEM
if (isset($_POST['remove'])) {
    $cart_id = (int)$_POST['cart_id'];

    $stmt = $conn->prepare(
        "DELETE FROM cart_items WHERE cart_id=? AND user_id=?"
    );
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();

    header("Location: cart.php");
    exit();
}

// UPDATE QUANTITY
if (isset($_POST['update'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = max(1, (int)$_POST['quantity']);

    $stmt = $conn->prepare(
        "UPDATE cart_items c
         JOIN products p ON c.product_id=p.product_id
         SET c.quantity=?
         WHERE c.cart_id=? AND c.user_id=? AND p.stock>=?"
    );
    $stmt->bind_param("iiii", $quantity, $cart_id, $user_id, $quantity);
    $stmt->execute();

    header("Location: cart.php");
    exit();
}

// GET CART
$stmt = $conn->prepare(
    "SELECT c.cart_id, c.quantity, p.product_id,
            p.name, p.brand, p.price, p.stock, p.image_url
     FROM cart_items c
     JOIN products p ON c.product_id=p.product_id
     WHERE c.user_id=?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result();

$items = [];
$total = 0;

while ($item = $cart->fetch_assoc()) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $items[] = $item;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Shopping Bag | Nykaa</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #fff7fa;
            font-family: Arial, sans-serif;
        }

        .cart-container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 20px;
        }

        .cart-container h2 {
            color: #e80071;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 3px 12px #eadce3;
            flex-wrap: wrap;
        }

        .cart-item img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
        }

        .cart-info {
            flex: 1;
        }

        .cart-info h3 {
            margin-bottom: 5px;
        }

        .cart-info p {
            color: #777;
        }

        .cart-item input {
            width: 65px;
            padding: 8px;
        }

        .cart-item button {
            background: #e80071;
            color: white;
            border: none;
            padding: 9px 14px;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn {
            background: #777 !important;
        }

        .cart-total {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: right;
            box-shadow: 0 3px 12px #eadce3;
        }

        .cart-total h2 {
            color: #e80071;
        }

        .cart-total a {
            display: inline-block;
            background: #e80071;
            color: white;
            padding: 13px 25px;
            border-radius: 6px;
            text-decoration: none;
        }

        .continue {
            color: #e80071;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<header>
    <div class="topbar">
        <h1>nykaa<span>.</span></h1>
        <div class="header-links">
            <a href="index.php">Home</a>
            <a href="products.php">Shop</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>

<div class="cart-container">

    <h2>My Shopping Bag 🛍️</h2>

    <?php if (count($items) > 0) { ?>

        <?php foreach ($items as $item) { ?>

            <div class="cart-item">

                <img src="<?php echo htmlspecialchars($item['image_url'] ?? ''); ?>"
                     alt="<?php echo htmlspecialchars($item['name']); ?>">

                <div class="cart-info">
                    <h3><?php echo htmlspecialchars($item['brand']); ?></h3>

                    <p><?php echo htmlspecialchars($item['name']); ?></p>

                    <strong>₹<?php echo number_format($item['price'], 2); ?></strong>

                    <p>Subtotal: ₹<?php echo number_format($item['subtotal'], 2); ?></p>

                    <form method="POST">
                        <input type="hidden" name="cart_id"
                               value="<?php echo $item['cart_id']; ?>">

                        <input type="number" name="quantity"
                               value="<?php echo $item['quantity']; ?>"
                               min="1" max="<?php echo $item['stock']; ?>"
                               required>

                        <button name="update">Update</button>
                        <button name="remove" class="remove-btn">Remove</button>
                    </form>
                </div>

            </div>

        <?php } ?>

        <div class="cart-total">
            <h2>Total: ₹<?php echo number_format($total, 2); ?></h2>
            <a href="checkout.php">PROCEED TO CHECKOUT</a>
        </div>

    <?php } else { ?>

        <h3>Your shopping bag is empty!</h3>
        <a href="products.php" class="shop-btn">CONTINUE SHOPPING</a>

    <?php } ?>

    <a href="products.php" class="continue">← Continue Shopping</a>

</div>

</body>
</html>
