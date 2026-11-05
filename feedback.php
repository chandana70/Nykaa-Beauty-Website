<?php
session_start();

$file = "feedback.txt";
$message = "";

// Read feedback records
function readFeedback($file) {

    $records = [];

    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $record = json_decode($line, true);

            if (is_array($record)) {
                $records[] = $record;
            }
        }
    }

    return $records;
}

// CREATE: Add feedback
if (isset($_POST['add'])) {

    $name = trim($_POST['name']);
    $feedback = trim($_POST['feedback']);

    if ($name != "" && $feedback != "") {

        $record = [
            "id" => uniqid(),
            "name" => $name,
            "feedback" => $feedback
        ];

        file_put_contents(
            $file,
            json_encode($record) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        $message = "Feedback added successfully!";
    }
}

// UPDATE: Edit feedback
if (isset($_POST['update'])) {

    $id = $_POST['id'];
    $records = readFeedback($file);

    foreach ($records as &$record) {
        if ($record['id'] === $id) {
            $record['name'] = trim($_POST['name']);
            $record['feedback'] = trim($_POST['feedback']);
        }
    }

    unset($record);

    $content = "";

    foreach ($records as $record) {
        $content .= json_encode($record) . PHP_EOL;
    }

    file_put_contents($file, $content, LOCK_EX);

    $message = "Feedback updated successfully!";
}

// DELETE: Remove feedback
if (isset($_POST['delete'])) {

    $id = $_POST['id'];
    $records = readFeedback($file);

    $records = array_filter($records, function($record) use ($id) {
        return $record['id'] !== $id;
    });

    $content = "";

    foreach ($records as $record) {
        $content .= json_encode($record) . PHP_EOL;
    }

    file_put_contents($file, $content, LOCK_EX);

    $message = "Feedback deleted successfully!";
}

$records = readFeedback($file);

// Get record for editing
$editRecord = null;

if (isset($_GET['edit'])) {
    foreach ($records as $record) {
        if ($record['id'] === $_GET['edit']) {
            $editRecord = $record;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Feedback | Nykaa</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .feedback-container {
            max-width: 750px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            box-shadow: 0 2px 12px #ddd;
            border-radius: 8px;
        }

        .feedback-container input,
        .feedback-container textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .feedback-card {
            background: #fff0f5;
            padding: 18px;
            margin: 15px 0;
            border-radius: 6px;
        }

        .feedback-card a {
            color: #e80071;
            margin-right: 15px;
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
            <a href="feedback.php">Feedback</a>
        </div>
    </div>
</header>

<div class="feedback-container">

    <h2>Customer Feedback</h2>
    <p>Share your experience with Nykaa.</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <h3>
        <?php echo $editRecord ? "Edit Feedback" : "Add Feedback"; ?>
    </h3>

    <form method="POST">

        <?php if ($editRecord) { ?>
            <input type="hidden" name="id"
                   value="<?php echo htmlspecialchars($editRecord['id']); ?>">
        <?php } ?>

        <input type="text" name="name"
               placeholder="Your Name"
               value="<?php echo htmlspecialchars($editRecord['name'] ?? ''); ?>"
               required>

        <textarea name="feedback"
                  placeholder="Write your feedback"
                  required><?php echo htmlspecialchars($editRecord['feedback'] ?? ''); ?></textarea>

        <?php if ($editRecord) { ?>

            <button type="submit" name="update" class="shop-btn">
                UPDATE FEEDBACK
            </button>

            <a href="feedback.php">Cancel</a>

        <?php } else { ?>

            <button type="submit" name="add" class="shop-btn">
                SUBMIT FEEDBACK
            </button>

        <?php } ?>

    </form>

    <hr>

    <h3>Customer Reviews</h3>

    <?php if (count($records) > 0) { ?>

        <?php foreach ($records as $record) { ?>

            <div class="feedback-card">

                <h3>
                    <?php echo htmlspecialchars($record['name']); ?>
                </h3>

                <p>
                    <?php echo htmlspecialchars($record['feedback']); ?>
                </p>

                <a href="feedback.php?edit=<?php echo urlencode($record['id']); ?>">
                    Edit
                </a>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id"
                           value="<?php echo htmlspecialchars($record['id']); ?>">

                    <button type="submit" name="delete"
                            onclick="return confirm('Delete this feedback?')">
                        Delete
                    </button>
                </form>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>No feedback available yet.</p>

    <?php } ?>

</div>

<footer>
    <h2>nykaa.</h2>
    <p>Beauty is for everyone.</p>
</footer>

</body>
</html>