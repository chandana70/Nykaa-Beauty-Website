<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interaction Demo | Nykaa</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .demo-container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 20px;
        }

        .demo-section {
            background: white;
            padding: 22px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px #ddd;
        }

        .coordinate-box {
            background: #fff0f5;
            padding: 18px;
            border-radius: 6px;
            min-height: 45px;
        }

        .help-input {
            padding: 12px;
            width: 90%;
            border: 1px solid #ccc;
            margin: 10px 0;
        }

        #helpText {
            color: #e80071;
            font-weight: bold;
            min-height: 25px;
        }

        .panel-area {
            position: relative;
            height: 230px;
        }

        .demo-panel {
            position: absolute;
            width: 220px;
            height: 140px;
            padding: 15px;
            border: 2px solid #e80071;
            border-radius: 8px;
            background: #fff0f5;
            cursor: pointer;
        }

        #panel1 {
            left: 20px;
            top: 15px;
            z-index: 1;
        }

        #panel2 {
            left: 160px;
            top: 55px;
            background: #ffe0ed;
            z-index: 2;
        }

        #panel3 {
            left: 300px;
            top: 90px;
            background: #ffd0e3;
            z-index: 3;
        }

        .filter-buttons button {
            padding: 10px 18px;
            margin: 5px;
            border: none;
            background: #e80071;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .demo-item {
            display: inline-block;
            padding: 15px;
            margin: 8px;
            background: #fff0f5;
            border: 1px solid #f3a4c8;
            border-radius: 5px;
        }

        @media(max-width: 650px) {
            .panel-area {
                height: 380px;
            }

            #panel1 { left: 5px; top: 5px; }
            #panel2 { left: 45px; top: 100px; }
            #panel3 { left: 85px; top: 195px; }
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
            <a href="wishlist.php">♡ Wishlist</a>
            <a href="interaction.php">Interaction Demo</a>
        </div>
    </div>
</header>

<div class="demo-container">

    <h2>JavaScript Events and DOM Demonstration</h2>
    <p>Explore the interactive features below.</p>

    <!-- 1. Mouse coordinates -->
    <section class="demo-section">
        <h3>1. Mouse Coordinate Tracker</h3>
        <p>Move your mouse inside this box to see its coordinates.</p>

        <div class="coordinate-box" id="mouseBox">
            Move your mouse here!
        </div>

        <p id="coordinates">Coordinates will appear here.</p>
    </section>

    <!-- 2. Keyboard event -->
    <section class="demo-section">
        <h3>2. Keyboard Event Demonstration</h3>
        <p>Type something in the box below.</p>

        <input class="help-input" type="text"
               id="keyboardInput"
               placeholder="Type here to detect keyboard events">

        <p id="keyOutput">Your keyboard activity will appear here.</p>
    </section>

    <!-- 3. Contextual help -->
    <section class="demo-section">
        <h3>3. Contextual Help</h3>
        <p>Click or focus on the input fields to get help.</p>

        <input class="help-input" type="text"
               placeholder="Enter your name"
               onfocus="showHelp('Enter your full name using letters only.')"
               onblur="showHelp('')">

        <input class="help-input" type="email"
               placeholder="Enter your email"
               onfocus="showHelp('Enter a valid email address, for example name@gmail.com.')"
               onblur="showHelp('')">

        <p id="helpText"></p>
    </section>

    <!-- 4. Overlapping panels -->
    <section class="demo-section">
        <h3>4. Hover-to-Front Panels</h3>
        <p>Move your mouse over a panel to bring it to the front.</p>

        <div class="panel-area">
            <div class="demo-panel" id="panel1">
                <h3>Panel 1</h3>
                Beauty Products
            </div>

            <div class="demo-panel" id="panel2">
                <h3>Panel 2</h3>
                Skincare Collection
            </div>

            <div class="demo-panel" id="panel3">
                <h3>Panel 3</h3>
                Makeup Collection
            </div>
        </div>
    </section>

    <!-- 5. Dynamic filtering -->
    <section class="demo-section">
        <h3>5. Dynamic Product Filtering</h3>
        <p>Select a category to filter the products instantly.</p>

        <div class="filter-buttons">
            <button onclick="filterItems('all')">All</button>
            <button onclick="filterItems('skincare')">Skincare</button>
            <button onclick="filterItems('makeup')">Makeup</button>
            <button onclick="filterItems('haircare')">Haircare</button>
        </div>

        <div id="demoProducts">
            <div class="demo-item" data-category="skincare">Vitamin C Serum</div>
            <div class="demo-item" data-category="skincare">Moisturizer</div>
            <div class="demo-item" data-category="makeup">Lipstick</div>
            <div class="demo-item" data-category="makeup">Mascara</div>
            <div class="demo-item" data-category="haircare">Hair Serum</div>
            <div class="demo-item" data-category="haircare">Shampoo</div>
        </div>
    </section>

</div>

<footer>
    <h2>nykaa.</h2>
    <p>Beauty is for everyone.</p>
</footer>

<script>

// 1. Mouse coordinates
let mouseBox = document.getElementById("mouseBox");

mouseBox.addEventListener("mousemove", function(event) {

    document.getElementById("coordinates").innerHTML =
        "Client X: " + event.clientX +
        " | Client Y: " + event.clientY +
        "<br>Screen X: " + event.screenX +
        " | Screen Y: " + event.screenY;
});


// 2. Keyboard events
document.getElementById("keyboardInput")
.addEventListener("keydown", function(event) {

    document.getElementById("keyOutput").innerHTML =
        "Key pressed: " + event.key;
});


// 3. Contextual help
function showHelp(message) {
    document.getElementById("helpText").innerHTML = message;
}


// 4. Hover-to-front panels
let panels = document.querySelectorAll(".demo-panel");
let highestZ = 3;

panels.forEach(function(panel) {

    panel.addEventListener("mouseenter", function() {
        highestZ++;
        panel.style.zIndex = highestZ;
    });

});


// 5. Dynamic filtering
function filterItems(category) {

    let items = document.querySelectorAll(".demo-item");

    items.forEach(function(item) {

        if (category === "all" ||
            item.dataset.category === category) {

            item.style.display = "inline-block";

        } else {

            item.style.display = "none";

        }

    });
}

</script>

</body>
</html>