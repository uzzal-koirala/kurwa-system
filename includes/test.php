<?php
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email == "admin@gmail.com" && $password == "1234") {
        $message = "Login successful!";
    } else {
        $message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Login</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #e6f0ec;
        }

        .container {
            width: 800px;
            margin: 80px auto;
            display: flex;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        .left {
            width: 50%;
            padding: 40px;
        }

        .right {
            width: 50%;
            background: #0f8a5f;
            color: white;
            text-align: center;
            padding: 40px 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            color: #555;
            font-size: 14px;
        }

        .right p {
            color: #d6f5ea;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .row a {
            text-decoration: none;
            color: #0f8a5f;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0f8a5f;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #0c6e4c;
        }

        .back {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: #555;
            font-size: 13px;
        }

        .message {
            margin-top: 10px;
            color: red;
        }

        .success {
            color: green;
        }

        img {
            width: 200px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- Left Side -->
    <div class="left">
        <h2>Sign in to Pharmacy</h2>
        <p>Welcome back! Manage your store inventory and orders.</p>

        <form method="POST">

            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>

            <div class="row">
                <label><input type="checkbox"> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit">Sign In</button>
        </form>

        <a href="#" class="back">← Back to Main Portal</a>

        <?php
        if ($message != "") {
            $class = ($message == "Login successful!") ? "success" : "message";
            echo "<p class='$class'>$message</p>";
        }
        ?>
    </div>

    <!-- Right Side -->
    <div class="right">
        <img src="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae" alt="medicine">
        <h3>Empower Your Medical Storefront</h3>
        <p>
            Join the largest network of verified healthcare and medicine providers in Nepal.
        </p>
    </div>

</div>

</body>
</html>