<?php
	include 'auth.php';
	include 'config.php';

	// Handle adding Brands
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['brand_title'])) {
		$brand_title = trim($_POST['brand_title']);
		
		$stmt = $conn->prepare("SELECT * FROM brands WHERE brand_title = ?");
		$stmt->bind_param("s", $brand_title);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$_SESSION['message'] = "<div class=\"notif\">Brand Already Exists!</div>";
		} else {
			$stmt->close();
			$stmt = $conn->prepare("INSERT INTO brands (brand_title) VALUES(?)");
			$stmt->bind_param("s", $brand_title);
			if ($stmt->execute()) {
				setToastr('success', 'Brand successfully added.', 'Registration Success!');
			} else {
				setToastr('error', 'Something went wrong.', 'Error!');
			}
		}
		$stmt->close();
		header("Location: manage_brands_categories.php");
		exit();
	}

	// Handle adding Categories
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cat_title'])) {
		$cat_title = trim($_POST['cat_title']);

		$stmt = $conn->prepare("SELECT * FROM category WHERE cat_title = ?");
		$stmt->bind_param("s", $cat_title);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$_SESSION['message'] = "<div class=\"notif\">Category Already Exists!</div>";
		} else {
			$stmt->close();
			$stmt = $conn->prepare("INSERT INTO category (cat_title) VALUES(?)");
			$stmt->bind_param("s", $cat_title);
			if ($stmt->execute()) {
				$_SESSION['message'] = "<div class=\"notif\">New Category Added Successfully!</div>";
			} else {
				$_SESSION['message'] = "<div class=\"notif\">Error: " . $stmt->error . "</div>";
			}
		}
		$stmt->close();
		header("Location: manage_brands_categories.php");
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

<div class="main-cont">
<main>
	<div class="head"><h2>Manage Brands & Categories</h2></div>
	<div class="wrapper">

		<!-- Brands Section -->
		<div class="cont">
			<h2>Add Brand</h2>
			<form action="manage_brands_categories.php" method="POST">
				<div class="form">
					<input type="text" name="brand_title" placeholder="Brand" required>
				</div>
				<div class="button">
					<input type="submit" value="Add Brand">
				</div>
			</form>
		</div>

		<div class="table">
			<h2>Brands List</h2>
			<?php
				$sql = "SELECT id, brand_title, DATE_FORMAT(created_at, '%M %d, %Y') AS created_at FROM brands";
				$result = $conn->query($sql);

				if ($result->num_rows > 0) : ?>
				<table>
					<tr>
						<th>Brand ID</th>
						<th>Brand Title</th>
						<th>Date Added</th>
						<th></th>
					</tr>
					<?php while ($row = $result->fetch_assoc()) : ?>
						<tr>
							<td><?= htmlspecialchars($row['id']) ?></td>
							<td><?= htmlspecialchars($row['brand_title']) ?></td>
							<td><?= htmlspecialchars($row['created_at']) ?></td>
							<td><a href="delete.php?table=brands&id=<?= htmlspecialchars($row['id']) ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a></td>
						</tr>
					<?php endwhile; ?>
				</table>
			<?php else : ?>
				<p>No brands found</p>
			<?php endif; ?>
		</div>

		<!-- Categories Section -->
		<div class="cont">
			<h2>Add Category</h2>
			<form action="manage_brands_categories.php" method="POST">
				<div class="form">
					<input type="text" name="cat_title" placeholder="Category" required>
				</div>
				<div class="button">
					<input type="submit" value="Add Category">
				</div>
			</form>
		</div>

		<div class="table">
			<h2>Categories List</h2>
			<?php
				$sql = "SELECT id, cat_title, DATE_FORMAT(created_at, '%M %d, %Y') AS created_at FROM category";
				$result = $conn->query($sql);

				if ($result->num_rows > 0) : ?>
				<table>
					<tr>
						<th>Category ID</th>
						<th>Category Title</th>
						<th>Date Added</th>
						<th></th>
					</tr>
					<?php while ($row = $result->fetch_assoc()) : ?>
						<tr>
							<td><?= htmlspecialchars($row['id']) ?></td>
							<td><?= htmlspecialchars($row['cat_title']) ?></td>
							<td><?= htmlspecialchars($row['created_at']) ?></td>
							<td><a href="delete.php?table=category&id=<?= htmlspecialchars($row['id']) ?>" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a></td>
						</tr>
					<?php endwhile; ?>
				</table>
			<?php else : ?>
				<p>No categories found</p>
			<?php endif; ?>
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
</body>
</html>
