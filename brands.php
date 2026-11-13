<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$brand_title = trim($_POST['brand_title']);
		
		$stmt = $conn->prepare("SELECT * FROM brands WHERE brand_title = ?");
		$stmt->bind_param("s", $brand_title);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			$_SESSION['message'] = "<div class=\"notif\">Brand Already Exists!</div>";
			exit();
		}else{
		$stmt->close();
		$stmt = $conn->prepare("INSERT INTO brands (brand_title) VALUES(?)");
		$stmt->bind_param("s", $brand_title);
		}
		
		if ($stmt->execute()) {
    setToastr('success', 'New Brand Added', 'Success!');
	} else {
    setToastr('error', 'Something went Wrong', 'Error!');
	}
	$stmt->close();
	header("Location: brands.php");
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
		<link rel="stylesheet" href="brands_categories.css">
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
	<div class="head"><h1> Manage Brands</h1></div>
	</header>
	<main>
		
		<div class="wrapper">
			<div class="cont">
				<h2>Add Brand</h2>
				<form action="brands.php" method="POST">
					<div class="form">
							<input type="text" id="title" name="brand_title" placeholder="Brand" required>
					</div>
					
						<button class="button" type="submit" >Add Brand </button>
				</form>
			</div>
			<div class="list-section">
			<h2>Brand List</h2>
			<div class="table-section">
				
				<?php
					$sql = "SELECT id,
					brand_title,
					DATE_FORMAT(created_at, '%M %d, %Y') AS created_at
					FROM brands";
					$result = $conn->query($sql);
					
					if($result->num_rows > 0) : ?>
					<table>
						<tr>
							<th>Brand ID</th>
							<th>Brand Title</th>
							<th>Date Added</th>
							<th></th>
							
						</tr>
						<?php while($row = $result->fetch_assoc()) : ?>
							<tr>
								<td><?= htmlspecialchars($row['id'])?></td>
								<td><?= htmlspecialchars($row['brand_title'])?></td>
								<td><?= htmlspecialchars($row['created_at']) ?></td>
								<td><a href="delete.php?table=brands&id=<?= htmlspecialchars($row['id']) ?>" class="desktop-only" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
								</td>
							</tr>
						<?php endwhile;?>
					</table>
				<?php else :?>
					<p>No brands found</p>
				<?php endif; ?>
				</div>
			</div>
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


