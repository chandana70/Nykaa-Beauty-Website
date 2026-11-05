<?php
include 'db.php';

$message = "";

// ADD PRODUCT
if (isset($_POST['add'])) {

    $category = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description']);

    if ($name != "" && $brand != "" && $price > 0 && $stock >= 0) {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (category_id, name, brand, price, stock, description)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issdis",
            $category, $name, $brand, $price, $stock, $description
        );

        $stmt->execute();
        header("Location: manage_products.php?msg=added");
        exit();

    } else {
        $message = "Please enter valid product details.";
    }
}

// UPDATE PRODUCT
if (isset($_POST['update'])) {

    $id = (int)$_POST['product_id'];
    $category = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description']);

    if ($name != "" && $brand != "" && $price > 0 && $stock >= 0) {

        $stmt = $conn->prepare(
            "UPDATE products
             SET category_id=?, name=?, brand=?, price=?, stock=?, description=?
             WHERE product_id=?"
        );

        $stmt->bind_param(
            "issdisi",
            $category, $name, $brand, $price, $stock, $description, $id
        );

        $stmt->execute();
        header("Location: manage_products.php?msg=updated");
        exit();

    } else {
        $message = "Please enter valid product details.";
    }
}

// DELETE PRODUCT
if (isset($_POST['delete'])) {

    $id = (int)$_POST['product_id'];

    // Remove wishlist references first
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_products.php?msg=deleted");
    exit();
}

// DISPLAY SUCCESS MESSAGE
if (isset($_GET['msg'])) {

    $messages = [
        "added" => "Product added successfully!",
        "updated" => "Product updated successfully!",
        "deleted" => "Product deleted successfully!"
    ];

    $message = $messages[$_GET['msg']] ?? "";
}

// GET CATEGORIES
$categories = $conn->query("SELECT * FROM categories");

// GET PRODUCT FOR EDITING
$editProduct = null;

if (isset($_GET['edit'])) {

    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $editProduct = $stmt->get_result()->fetch_assoc();
}

// DISPLAY ALL PRODUCTS
$products = $conn->query(
    "SELECT p.*, c.category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.category_id
     ORDER BY p.product_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Products | Nykaa</title>
    <link rel="stylesheet" href="style.css">

    
<style>
    body {
        background: #fff7fa;
        font-family: Arial, sans-serif;
        margin: 0;
        color: #333;
    }

    header {
        background: white;
        box-shadow: 0 2px 10px #eadce3;
        padding: 15px 5%;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .topbar h1 {
        color: #e80071;
        font-size: 34px;
        margin: 0;
    }

    .header-links {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
    }

    .header-links a {
        color: #444;
        text-decoration: none;
        font-weight: bold;
    }

    .header-links a:hover {
        color: #e80071;
    }

    .admin-container {
        max-width: 1100px;
        margin: 40px auto;
        padding: 20px;
    }

    .admin-container h2 {
        color: #e80071;
        font-size: 30px;
        margin-bottom: 5px;
    }

    .admin-container > p {
        color: #777;
        margin-bottom: 25px;
    }

    .admin-form {
        background: white;
        padding: 30px;
        box-shadow: 0 5px 20px #eadce3;
        border-radius: 14px;
        margin-bottom: 40px;
    }

    .admin-form h3 {
        color: #c90063;
        margin-top: 0;
        margin-bottom: 22px;
    }

    .admin-form label {
        display: block;
        font-weight: bold;
        margin-top: 15px;
        color: #444;
    }

    .admin-form input,
    .admin-form select,
    .admin-form textarea {
        width: 100%;
        padding: 13px;
        margin-top: 7px;
        box-sizing: border-box;
        border: 1px solid #e5d5dd;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
    }

    .admin-form input:focus,
    .admin-form select:focus,
    .admin-form textarea:focus {
        border-color: #e80071;
        box-shadow: 0 0 5px #f7b5d4;
    }

    .admin-form textarea {
        min-height: 90px;
        resize: vertical;
    }

    .shop-btn,
    .admin-table button {
        background: #e80071;
        color: white;
        border: none;
        padding: 12px 22px;
        border-radius: 7px;
        cursor: pointer;
        font-weight: bold;
        margin-top: 18px;
    }

    .shop-btn:hover,
    .admin-table button:hover {
        background: #c90063;
    }

    .admin-container > h3 {
        color: #c90063;
        margin-bottom: 18px;
    }

    .table-wrapper {
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px #eadce3;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 750px;
    }

    .admin-table th {
        background: #ffe0ed;
        color: #9e004d;
        padding: 16px;
        text-align: left;
    }

    .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1e5eb;
    }

    .admin-table tr:hover {
        background: #fff5f9;
    }

    .admin-table a {
        color: #e80071;
        font-weight: bold;
        text-decoration: none;
        margin-right: 10px;
    }

    .admin-table button {
        padding: 8px 13px;
        margin: 0;
    }

    .message {
        background: #e8f8ed;
        color: #217a3c;
        padding: 13px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    @media(max-width: 600px) {
        .admin-container {
            padding: 12px;
            margin: 20px auto;
        }

        .admin-form {
            padding: 18px;
        }
    }
</style>

</head>

<body>

<header>
    <div class="topbar">
        <h1>nykaa<span>.</span></h1>

        <div class="header-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="wishlist.php">Wishlist</a>
        </div>
    </div>
</header>

<div class="admin-container">

    <h2>Product Management</h2>
    <p>Add, view, update and delete products.</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <div class="admin-form">

        <h3>
            <?php echo $editProduct ? "Edit Product" : "Add New Product"; ?>
        </h3>

        <form method="POST">

            <?php if ($editProduct) { ?>
                <input type="hidden" name="product_id"
                       value="<?php echo $editProduct['product_id']; ?>">
            <?php } ?>

            <label>Product Name</label>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>">

            <label>Brand</label>
            <input type="text" name="brand" required
                   value="<?php echo htmlspecialchars($editProduct['brand'] ?? ''); ?>">

            <label>Category</label>
            <select name="category_id" required>

                <?php while ($cat = $categories->fetch_assoc()) { ?>

                    <option value="<?php echo $cat['category_id']; ?>"
                        <?php
                        if ($editProduct &&
                            $editProduct['category_id'] == $cat['category_id']) {
                            echo "selected";
                        }
                        ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>

                <?php } ?>

            </select>

            <label>Price (₹)</label>
            <input type="number" name="price" min="0.01"
                   step="0.01" required
                   value="<?php echo $editProduct['price'] ?? ''; ?>">

            <label>Stock Quantity</label>
            <input type="number" name="stock" min="0" required
                   value="<?php echo $editProduct['stock'] ?? ''; ?>">

            <label>Description</label>
            <textarea name="description" required><?php
                echo htmlspecialchars($editProduct['description'] ?? '');
            ?></textarea>

            <?php if ($editProduct) { ?>

                <button type="submit" name="update" class="shop-btn">
                    UPDATE PRODUCT
                </button>

                <a href="manage_products.php">Cancel</a>

            <?php } else { ?>

                <button type="submit" name="add" class="shop-btn">
                    ADD PRODUCT
                </button>

            <?php } ?>

        </form>

    </div>

    <h3>All Products</h3>

    <div class="table-wrapper">

        <table class="admin-table">

            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>

            <?php while ($p = $products->fetch_assoc()) { ?>

                <tr>
                    <td><?php echo $p['product_id']; ?></td>

                    <td><?php echo htmlspecialchars($p['name']); ?></td>

                    <td><?php echo htmlspecialchars($p['brand']); ?></td>

                    <td><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></td>

                    <td>₹<?php echo $p['price']; ?></td>

                    <td><?php echo $p['stock']; ?></td>

                    <td>
                        <a href="manage_products.php?edit=<?php echo $p['product_id']; ?>">
                            Edit
                        </a>

                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Delete this product?');">

                            <input type="hidden" name="product_id"
                                   value="<?php echo $p['product_id']; ?>">

                            <button type="submit" name="delete">
                                Delete
                            </button>

                        </form>
                    </td>
                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>