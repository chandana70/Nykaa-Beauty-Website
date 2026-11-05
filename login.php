<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];

            header("Location: index.php");
            exit();

        } else {
            $message = "Incorrect password.";
        }

    } else {
        $message = "Account not found. Please register first.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Nykaa</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <div class="topbar">
        <h1>nykaa<span>.</span></h1>
        <div class="header-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
        </div>
    </div>
</header>

<div class="auth-container">

    <h2>Welcome Back!</h2>
    <p>Login to your Nykaa account</p>

    <?php
    if (isset($_GET['registered'])) {
        echo "<p class='message'>Registration successful! Please login.</p>";
    }
    ?>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST">

        <input type="email" name="email"
               placeholder="Email Address" required>

        <input type="password" name="password"
               placeholder="Password" required>

        <button type="submit" class="shop-btn">
            LOGIN
        </button>

    </form>

    <p>New to Nykaa?
        <a href="register.php">Create Account</a>
    </p>

</div>

</body>
</html>