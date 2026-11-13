<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>UtangSavvy</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="header_footer_style.css">
		<link rel="stylesheet" href="usermanagement.css">
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
		<div class="head"><h1>User Management</h1></div>
	</header>
	<main>
		<div class="wrapper">
		<h2> Users</h2>
		<div class="table-section">
			
			<?php
				$sql = "Select * FROM users";
				$result = $conn->query($sql);
				
				if($result->num_rows > 0) : ?>
				<table>
				<thead>
					<tr>
						<th>id</th>
						<th>Username</th>
						<th>Password</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php while($row = $result->fetch_assoc()) : ?>
						<tr>
							<td><?= htmlspecialchars($row['id'])?></td>
							<td><?= htmlspecialchars($row['username'])?></td>
							<td>•••••••</td>
							<td>
							<a href="edit_user.php?id=<?=htmlspecialchars($row['id']) ?>" class="desktop-only">Edit</a>
							<a href="delete.php?table=users&id=<?= htmlspecialchars($row['id']) ?>" class="desktop-only" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
							
							
							</td>
						</tr>
					<?php endwhile;?>
				</tbody>
				</table>
				
			<?php else :?>
				<p>No User found</p>
			<?php endif; ?>
			</div>
						<a href="add_user.php" class="button">Add User</a>
					
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