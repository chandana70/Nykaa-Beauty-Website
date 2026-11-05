
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// GET CART ITEMS
$stmt = $conn->prepare(
    "SELECT c.product_id, c.quantity, p.name, p.price, p.stock
     FROM cart_items c
     JOIN products p ON c.product_id=p.product_id
     WHERE c.user_id=?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    $items[] = $item;
    $total += $item['price'] * $item['quantity'];
}

if (count($items) == 0) {
    header("Location: cart.php");
    exit();
}

// PLACE ORDER
if (isset($_POST['place_order'])) {

    $name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $pincode = trim($_POST['pincode']);
    $payment = $_POST['payment_method'] ?? '';

    if (
        $name == "" || !preg_match("/^[0-9]{10}$/", $phone) ||
        $address == "" || $city == "" ||
        !preg_match("/^[0-9]{6}$/", $pincode) ||
        !in_array($payment, ['COD', 'UPI Demo'])
    ) {
        $error = "Please enter valid delivery and payment details.";
    } else {

        $conn->begin_transaction();

        try {
            // Recheck stock and price inside the transaction
            $check = $conn->prepare(
                "SELECT c.product_id, c.quantity, p.name, p.price, p.stock
                 FROM cart_items c
                 JOIN products p ON c.product_id=p.product_id
                 WHERE c.user_id=?
                 FOR UPDATE"
            );
            $check->bind_param("i", $user_id);
            $check->execute();
            $freshResult = $check->get_result();

            $freshItems = [];
            $freshTotal = 0;

            while ($item = $freshResult->fetch_assoc()) {
                if ($item['quantity'] > $item['stock']) {
                    throw new Exception("Insufficient stock for " . $item['name']);
                }

                $freshItems[] = $item;
                $freshTotal += $item['price'] * $item['quantity'];
            }

            if (count($freshItems) == 0) {
                throw new Exception("Your cart is empty.");
            }

            // Save order
            $orderStmt = $conn->prepare(
                "INSERT INTO orders
                 (user_id, customer_name, phone, address, city,
                  pincode, payment_method, total_amount)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $orderStmt->bind_param(
                "issssssd",
                $user_id, $name, $phone, $address,
                $city, $pincode, $payment, $freshTotal
            );
            $orderStmt->execute();

            $order_id = $conn->insert_id;

            foreach ($freshItems as $item) {

                $itemStmt = $conn->prepare(
                    "INSERT INTO order_items
                     (order_id, product_id, product_name, price, quantity)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $itemStmt->bind_param(
                    "iisdi",
                    $order_id,
                    $item['product_id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity']
                );
                $itemStmt->execute();

                // Reduce available stock
                $stockStmt = $conn->prepare(
                    "UPDATE products
                     SET stock = stock - ?
                     WHERE product_id=? AND stock>=?"
                );
                $stockStmt->bind_param(
                    "iii",
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity']
                );
                $stockStmt->execute();

                if ($stockStmt->affected_rows != 1) {
                    throw new Exception("Stock changed. Please try again.");
                }
            }

            // Empty customer's cart
            $delete = $conn->prepare(
                "DELETE FROM cart_items WHERE user_id=?"
            );
            $delete->bind_param("i", $user_id);
            $delete->execute();

            $conn->commit();

            header("Location: order_success.php?id=" . $order_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout | Nykaa</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #fff7fa;
            font-family: Arial, sans-serif;
        }

        .checkout-container {
            max-width: 850px;
            margin: 35px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px #eadce3;
        }

        .checkout-container h2,
        .checkout-container h3 {
            color: #e80071;
        }

        .checkout-container label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        .checkout-container input,
        .checkout-container textarea,
        .checkout-container select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        .checkout-container textarea {
            min-height: 90px;
        }

        .checkout-total {
            background: #fff0f6;
            padding: 18px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .checkout-container button {
            background: #e80071;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 14px 24px;
            margin-top: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .checkout-error {
            color: #b00020;
            background: #ffe8ec;
            padding: 12px;
            border-radius: 7px;
        }
    </style>
</head>

<body>

<header>
    <div class="topbar">
        <h1>nykaa<span>.</span></h1>
        <div class="header-links">
            <a href="index.php">Home</a>
            <a href="cart.php">Shopping Bag</a>
        </div>
    </div>
</header>

<div class="checkout-container">

    <h2>Delivery & Payment</h2>
    <p>Enter your delivery details to place your order.</p>

    <?php if ($error != "") { ?>
        <p class="checkout-error">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php } ?>

    <h3>Order Summary</h3>

    <?php foreach ($items as $item) { ?>
        <p>
            <?php echo htmlspecialchars($item['name']); ?>
            × <?php echo $item['quantity']; ?>
            — ₹<?php echo number_format(
                $item['price'] * $item['quantity'], 2
            ); ?>
        </p>
    <?php } ?>

    <div class="checkout-total">
        <h3>Total: ₹<?php echo number_format($total, 2); ?></h3>
    </div>

    <form method="POST">

        <h3>Delivery Address</h3>

        <label>Full Name</label>
        <input type="text" name="customer_name" required
               value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>">

        <label>Mobile Number</label>
        <input type="tel" name="phone" pattern="[0-9]{10}"
               maxlength="10" required
               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">

        <label>Address</label>
        <textarea name="address" required><?php
            echo htmlspecialchars($_POST['address'] ?? '');
        ?></textarea>

        <label>City</label>
        <input type="text" name="city" required
               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">

        <label>PIN Code</label>
        <input type="text" name="pincode"
               pattern="[0-9]{6}" maxlength="6" required
               value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>">

        <h3>Payment Method</h3>

        <label>Choose Payment</label>
        <select name="payment_method" required>
            <option value="">Select Payment Method</option>
            <option value="COD">Cash on Delivery</option>
            <option value="UPI Demo">UPI (Demo Payment)</option>
        </select>

        <button type="submit" name="place_order">
            PLACE ORDER
        </button>

    </form>

</div>

</body>
</html>
