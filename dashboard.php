<?php
	include 'auth.php';
	include 'helper.php';

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>UtangSavvy</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="header_footer_style.css">
		<link rel="stylesheet" href="dashboard.css">
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
		<div class="head"><h1>Dashboard</h1></div>
	</header>
	<main>
		
		<section class="boxlinks">
			<a href="ledger.php">
				<div class="index-boxlink-box">
					<div class="img">
						<img src="img/ledger.png">
					</div>
					<h3>View Ledgers</h3>
				</div>
			</a>
			<a href="product_inventory.php">				
				<div class="index-boxlink-box">
					<div class="img">
						<img src="img/box.png">
					</div>
					<h3>Product List</h3>
				</div>
			</a>

			<a href="categories.php">
				<div class="index-boxlink-box">
					<div class="img">
						<img src="img/folder.png">
					</div>
					<h3>Manage Categories</h3>
				</div>
			</a>

			<a href="brands.php">
				<div class="index-boxlink-box">
					<div class="img">
						<img src="img/tag.png">
					</div>
					<h3>Manage Brands</h3>
				</div>
			</a>

			
			<a href="usermanagement.php">
				<div class="index-boxlink-box">
					<div class="img">
						<img src="img/group.png">
					</div>
					<h3>Manage Users</h3>
				</div>
			</a>
			<a href="report.php">
				<div class="index-boxlink-box">
					<div class="img">
						<img src="img/report.png">
					</div>
					<h3>Transactions</h3>
				</div>
			</a>
		</section>
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
