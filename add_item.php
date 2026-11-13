<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	$categoryQuery = "SELECT id, cat_title FROM category";
	$categoryResult = $conn->query($categoryQuery);
	
	$brandQuery = "SELECT id, brand_title FROM brands";
	$brandResult = $conn->query($brandQuery);
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		
	$title = trim($_POST['product_title']);
	$desc = trim($_POST['product_desc']);
	$price = trim($_POST['product_price']);
	$quantity = trim($_POST['quantity']);
	$category = trim($_POST['product_cat']);
	$brand = trim($_POST['product_brand']);
	
		$sql = "SELECT * FROM products WHERE product_title = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $title);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			$_SESSION['message'] = "<div class=\"notif\">Product Already Exists!</div>";
			header("Location: " . $_SERVER['HTTP_REFERER']);
			exit();
			
		}else{
	$img = null;

	if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] == 0) {
    $targetDir = "uploads/";
    $photoPath = $targetDir . basename($_FILES['product_img']['name']);

    if (move_uploaded_file($_FILES['product_img']['tmp_name'], $photoPath)) {
        $img = $photoPath;
		} else {
        setToastr('error', 'Failed to move upload file.', 'Upload Error!');
        header("Location: " . $_SERVER['HTTP_REFERER']);
		exit;
		}
	} else {
    setToastr('error', 'No Image uploaded.', 'Upload Error!');
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
	}


	$sql = "INSERT INTO products 
			(product_title, 
			product_desc, 
			product_price,
			quantity,
			product_img, 
			product_cat, 
			product_brand) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssdsiii", $title, $desc, $price, $quantity, $img, $category, $brand);
	

	if ($stmt->execute()) {
    $_SESSION['message'] = "<div class=\"notif\">New Record Added Successfully!</div>";
	} else {
    $_SESSION['message'] = "<div class=\"notif\">Error: " . $stmt->error . "</div>";
	}
	}
	$stmt->close();
	$conn->close();
	
	header("Location: product_inventory.php");
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
			<div class="head"><h1>Add Product</h1></div>
	</header>
	<main>
			
		<div class="addItem">
			<form action="add_item.php" method="post" enctype="multipart/form-data">
				<div>
					<label for="product_title">Product Name:</label>
						<input type="text" id="product_title" name="product_title" placeholder="Product Name" required>
				</div>
				<div>
						<label for="product_desc">Product Description:</label>
							<textarea id="product_desc" name="product_desc" placeholder="Product Description" required></textarea>
				</div>
				<div>
					<label for="product_price">Product Price:</label>
						<input type="number" id="product_price" name="product_price" step="0.01" min="0" placeholder="₱ Price" required>
				</div>
				<div>
					<label for="quantity">Stock Count:</label>
						<input type="number" id="quantity" name="quantity" placeholder="Qty" required>
				</div>
				<div>
					<label for="category">Category:</label>
						<select name="product_cat" id="category" required>
							<option value="" disabled selected>Select Category</option>
							<?php while ($row = $categoryResult->fetch_assoc()) : ?>
							<option value="<?= $row['id']; ?>"><?= $row['cat_title'];?></option>
							<?php endwhile; ?>
						</select>
				</div>
				<div>
					<label for="brand"> Brand: </label>
						<select name="product_brand" id="brand" required>
							<option value="" disabled selected>Select Brand</option>
							<?php while ($row = $brandResult->fetch_assoc()) : ?>
							<option value="<?= $row['id']; ?>"><?= $row['brand_title'];?></option>
							<?php endwhile; ?>
						</select>
				</div>
				<div>
					<label for="product_img">Product Image:</label>
							<input type="file" id="product_img" name="product_img" required>
				</div>
				
					<input class="submit" type="submit"  value="Add Product">

			</form>
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
