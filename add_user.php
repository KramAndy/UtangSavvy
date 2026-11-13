<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$username = trim($_POST['username']);
		$password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
		
		$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			setToastr('error', 'Please choose another.', 'Username Already Exists!');
			header("Location: " . $_SERVER['HTTP_REFERER']);
			exit();
			
		}else{
		$stmt->close();
		$sql = "INSERT INTO users (username, password) 		VALUES(?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $username, $password);
		}
		
	if($stmt->execute()){
		 setToastr('success', 'New user added.', 'Success!');
	}else {
		setToastr('error', 'Please try again later.', 'Something Went Wrong!');
	}
	$stmt->close();
	header("Location: usermanagement.php");
	exit();
}
	
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>UtangSavvy</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="header_footer_style.css">
		<link rel="stylesheet" href="add_item.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	</head>
	<body>
	<div class="head-cont">
	<header>
		<div class="hamburger" onclick="toggleSidebar()">☰</div>
		<nav>
				<div class="logo">
					<a class="logo" href="dashboard.php"><img src="img/logo.png"></a>
				</div>
			<ul>
				<li>
					<a href="dashboard.php"> DASHBOARD</a>
				</li>
				<li>
				  <a href="ledger.php"> LEDGER</a>
				</li>
				<li><a href="product_inventory.php">PRODUCTS</a>
				</li>
				<li>
				   <div class="manage-container">
					<select id="manage" onchange="redirectPage(this)">
					  <option value=" " disabled selected>PRODUCT SETTINGS</option>
					  <option value="brands.php">BRANDS</option>
					  <option value="categories.php">CATEGORIES</option>
					</select>
				  </div>
				  <script>
				function redirectPage(selectElement) {
					let selectedValue = selectElement.value;

					if (selectedValue && selectedValue !== " ") { 
						window.location.href = selectedValue;
						}
					}
				</script>
				</li>
				
				<li><a href="usermanagement.php">USER MANAGEMENT</a>
				</li>
				<li><a href="report.php">TRANSACTIONS</a>
				</li>
				<li><a href="logout.php">LOGOUT</a>
				</li>
				
			</ul>
		</nav>
		<div class="head"><h1>Add New User</h1></div>
	</header>
	<main>
			
		<div class="addItem">
			
				<form action="add_user.php" method="post">
				<div>
					<label for="eusername">Username: </label>
					<input type="text" id="eusername" name="username" placeholder="Username" required>
				</div>
				<div>
					<label for="epassword">Password: </label>
					<input type="password" id="epassword" name="password" placeholder="Password" required>
				</div>
					<input class="submit" type="submit"  value="Add User">
		</div>
	</main>
	<footer>
		<p>© UtangSavvy.com v1.0 All rights reserved (2025)</p>
		</footer>
</div>
		<script>
			function toggleSidebar() {
				document.querySelector("nav").classList.toggle("active");
			}
		</script>
		<?php showToastr(); ?>
	</body>
</html>