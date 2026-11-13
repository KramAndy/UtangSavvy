<?php
session_start();
include 'config.php';
include 'helper.php';
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['id'] = $id;
            $_SESSION['username'] = $username;
            setToastr('success', 'Welcome back, ' . htmlspecialchars($username) . '!', 'Login Successful');
            header("Location: dashboard.php");
            exit();
        } else {
            setToastr('error', 'Invalid Password!', 'Login Failed');
        }
    } else {
        setToastr('error', 'Invalid Username!', 'Login Failed');
    }

    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE>
<html>
	<head>
		<meta charset="utf-8">
		<title> UtangSavvy</title>
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
					<a class="logo" href="dashboard.php"><img src="img/logo.png"></a>
				</div>
			
		</nav>
	</header>
	
	<main>
		<div class="container">
			<h2>Login</h2>
		<div class="cont">	
			<form action="index.php" method="post">
				<div class="login_form">
					<label for="username">Username: </label>
					<input type="text" id="username" name="username" placeholder="Username" required>
				</div>
				<div class="login_form">
					<label for="password">Password: </label>
					<input type="password" id="password" name="password" placeholder="Password" required>
				</div>
				<button class="showPass" type="button" onclick="togglePassword()">👁 Show Password</button>
			<script>
				function togglePassword() {
					let passwordField = document.getElementById("password");
					let button = document.querySelector(".showPass");

					if (passwordField.type === "password") {
						passwordField.type = "text";
						button.classList.add("activeShowPass");
						button.textContent = "👁 Hide Password";
					} else {
						passwordField.type = "password";
						button.classList.remove("activeShowPass");
						button.textContent = "👁 Show Password";
					}
				}
			</script>
					<button type="submit" class="button">Login</button>
					<a href="register.php" class="reg">Register</a>
			</form>
					

		</div>
			
		</div>
		
	</main>
	<footer>
		<p>© UtangSavvy.com v1.0 All rights reserved (2025)</p>
		</footer>
</div>
	<?php showToastr(); ?>

	</body>
</html>
