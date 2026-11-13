<?php
session_start();
include 'config.php';
include 'helper.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prevent empty submissions (just in case)
    if (empty($username) || empty($password)) {
        setToastr('error', 'Please fill all fields.', 'All Fields Required!');
        header("Location: register.php");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        setToastr('error', 'Please choose another.', 'Username Already Exists!');
        $stmt->close();
        header("Location: register.php");
        exit();
    }

    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);

    if ($stmt->execute()) {
        setToastr('success', 'You can now Login', 'Registration Successful!');
        header("Location: index.php");
    } else {
         setToastr('error', 'Please try again later.', 'Something Went Wrong!');
        header("Location: register.php");
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>UtangSavvy - Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>
<div class="main-cont">
    <header>
        <nav>
            <div class="logo">
                <a class="logo" href="#"><img src="img/logo.png" alt="UtangSavvy Logo"></a>
            </div>
        </nav>
    </header>
    <main>
        <div class="container">
            <h2>Register</h2>

            <form action="register.php" method="post">
                <div class="login_form">
                    <label for="eusername">Username:</label>
                    <input type="text" id="eusername" name="username" placeholder="Username" required>
                </div>
                <div class="login_form">
                    <label for="epassword">Password:</label>
                    <input type="password" id="epassword" name="password" placeholder="Password" required>
                </div>
                <button class="button" type="submit">Register</button>
            </form>

            <p style="text-align:center; margin-top:15px;">Already have an account? 
                <a href="index.php">Login here</a>
            </p>
        </div>
    </main>
    <footer>
        <p>© UtangSavvy.com v1.0 All rights reserved (2025)</p>
    </footer>
</div>
<?php showToastr(); ?>
</body>
</html>
