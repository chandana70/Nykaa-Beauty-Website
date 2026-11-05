
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int)$_GET['id'];

$stmt = $conn->prepare(
    "SELECT * FROM orders WHERE order_id=? AND user_id=?"
);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$stmt = $conn->prepare(
    "SELECT * FROM order_items WHERE order_id=?"
);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmed | Nykaa</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #fff7fa;
            font-family: Arial, sans-serif;
        }

        .success-box {
            max-width: 650px;
            margin: 60px auto;
            padding: 35px;
            background: white;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 4px 20px #eadce3;
        }

        .success-box h1 {
            color: #e80071;
        }

        .success-box p {
            line-height: 1.7;
        }

        .success-box a {
            display: inline-block;
            background: #e80071;
            color: white;
            text-decoration: none;
            padding: 13px 24px;
            border-radius: 7px;
            margin-top: 15px;
        }

        .order-items {
            text-align: left;
            background: #fff0f6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="success-box">

    <h1>✓ Order Placed Successfully!</h1>

    <p>Thank you for shopping with Nykaa.</p>

    <h3>Order Number: #<?php echo $order['order_id']; ?></h3>

    <p>
        Payment Method:
        <?php echo htmlspecialchars($order['payment_method']); ?>
    </p>

    <p>
        Delivery Address:
        <?php echo htmlspecialchars($order['address']); ?>,
        <?php echo htmlspecialchars($order['city']); ?> -
        <?php echo htmlspecialchars($order['pincode']); ?>
    </p>

    <div class="order-items">
        <h3>Ordered Products</h3>

        <?php while ($item = $items->fetch_assoc()) { ?>
            <p>
                <?php echo htmlspecialchars($item['product_name']); ?>
                × <?php echo $item['quantity']; ?>
                — ₹<?php echo number_format(
                    $item['price'] * $item['quantity'], 2
                ); ?>
            </p>
        <?php } ?>

        <h3>Total: ₹<?php echo number_format($order['total_amount'], 2); ?></h3>
    </div>

    <a href="index.php">CONTINUE SHOPPING</a>

</div>

</body>
</html>
