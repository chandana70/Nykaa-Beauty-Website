<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Nykaa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 900px;
            margin: 35px auto;
            padding: 20px;
        }
        .order-card {
            background: white;
            border: 1px solid #f3c5d8;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        .order-card h3 {
            color: #e80071;
        }
        .order-status {
            color: #16834a;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="orders-container">
    <h2>My Orders 📦</h2>

    <?php if ($orders->num_rows == 0) { ?>
        <p>You haven't placed any orders yet.</p>
        <a href="products.php">Start Shopping</a>
    <?php } ?>

    <?php while ($order = $orders->fetch_assoc()) { ?>
        <div class="order-card">
            <h3>Order #<?php echo $order['order_id']; ?></h3>

            <p><b>Date:</b>
                <?php echo htmlspecialchars($order['order_date']); ?>
            </p>

            <p><b>Total:</b> ₹<?php echo $order['total_amount']; ?></p>

            <p><b>Payment:</b>
                <?php echo htmlspecialchars($order['payment_method']); ?>
            </p>

            <p><b>Status:</b>
                <span class="order-status">
                    <?php echo htmlspecialchars($order['order_status']); ?>
                </span>
            </p>

            <hr>
            <b>Products:</b>

            <?php
            $order_id = $order['order_id'];

            $items = $conn->prepare(
                "SELECT product_name, price, quantity
                 FROM order_items WHERE order_id = ?"
            );
            $items->bind_param("i", $order_id);
            $items->execute();
            $itemResult = $items->get_result();

            while ($item = $itemResult->fetch_assoc()) {
            ?>
                <p>
                    <?php echo htmlspecialchars($item['product_name']); ?>
                    × <?php echo $item['quantity']; ?>
                    — ₹<?php echo $item['price']; ?>
                </p>
            <?php } ?>
        </div>
    <?php } ?>

    <a href="products.php">Continue Shopping</a>
</div>

</body>
</html>