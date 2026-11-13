<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		
		$sql = "SELECT * FROM users WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$user = $result->fetch_assoc();
		$stmt->close();
	}
	
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$id = $_POST['id'];
	$username = trim($_POST['username']);
	$password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
	
	$sql = "SELECT * FROM users WHERE username = ? AND id != ?";
	$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $username, $id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			setToastr('error', 'Please Choose another usernam.', 'Username Already Exists!');
			header("Location: " . $_SERVER['HTTP_REFERER']);
			exit();
		}else{
		$stmt->close();
		$sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sss", $username, $password, $id);
		}
	if($stmt->execute()){
		 setToastr('success', 'User updated successfully.', 'Success!');
	}else {
		setToastr('error', 'Something went wrong.', 'Error!');
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

	</header>
	<main>
		<div class="head"><h2>Edit User</h2></div>
		<div class="addItem">
			
				<form action="edit_user.php" method="post">
				<input type="hidden" name="id" value="<?= htmlspecialchars($user['id']); ?>">
				<div class="individual-input">
					<label for="eusername">New Username: </label>
					<input type="text" id="eusername" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
				</div>
				<div class="individual-input">
					<label for="epassword">New Password: </label>
					<input type="password" id="epassword" name="password" required>
				</div>
				
					<input type="submit" class="submit" value="Update User">
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