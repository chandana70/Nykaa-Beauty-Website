<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $usn = strtoupper(trim($_POST['usn']));
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!preg_match("/^[A-Za-z ]{3,50}$/", $name)) {
        $message = "Enter a valid name.";
    }
    elseif (!preg_match("/^[1-4][A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{3}$/", $usn)) {
        $message = "Enter a valid USN.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email.";
    }
    elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
    }
    else {
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email=? OR usn=?"
        );
        $check->bind_param("ss", $email, $usn);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $message = "Email or USN already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, usn, email, password)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss", $name, $usn, $email, $hashedPassword
            );

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register | Nykaa</title>
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

    <h2>Create Account</h2>
    <p>Join Nykaa Beauty</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" onsubmit="return validateForm()">

        <input type="text" id="name" name="name"
               placeholder="Full Name" required>

        <input type="text" id="usn" name="usn"
               placeholder="College USN" required>

        <input type="email" id="email" name="email"
               placeholder="Email Address" required>

        <input type="password" id="password" name="password"
               placeholder="Password (minimum 6 characters)" required>

        <button type="submit" class="shop-btn">
            REGISTER
        </button>

    </form>

    <p>Already have an account?
        <a href="login.php">Login</a>
    </p>

</div>

<script>
function validateForm() {

    let name = document.getElementById("name").value.trim();
    let usn = document.getElementById("usn").value.trim().toUpperCase();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value;

    let namePattern = /^[A-Za-z ]{3,50}$/;
    let usnPattern = /^[1-4][A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{3}$/;

    if (!namePattern.test(name)) {
        alert("Enter a valid name.");
        return false;
    }

    if (!usnPattern.test(usn)) {
        alert("Enter a valid USN.");
        return false;
    }

    if (!email.includes("@") || !email.includes(".")) {
        alert("Enter a valid email.");
        return false;
    }

    if (password.length < 6) {
        alert("Password must have at least 6 characters.");
        return false;
    }

    return true;
}
</script>

</body>
</html>