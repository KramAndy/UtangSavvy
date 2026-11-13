<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	$categoryQuery = "SELECT id, cat_title FROM category";
	$categoryResult = $conn->query($categoryQuery);
	
	$brandQuery = "SELECT id, brand_title FROM brands";
	$brandResult = $conn->query($brandQuery);
	
	if(isset($_GET['id'])){
		$id=$_GET['id'];
	
	
		$sql = "SELECT * FROM products WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$old_product_img = '';
		if($result->num_rows > 0){
			$product = $result->fetch_assoc();
			$old_product_img = $product['product_img'];
			
		}else {
			setToastr('error', 'Product not found.', 'Error!');
			 exit();
		}
	} else{
		setToastr('error', 'No Product found in the URL', 'Error!');
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
		<div class="head"> <h1>Edit Product</h1> </div>
	</header>
	<main>
		<div class="addItem">
			
			<form action="update_product.php" method="post" enctype="multipart/form-data">
					<input type="hidden" name="id" value="<?= htmlspecialchars($product['id']); ?>">
					<input type="hidden" name="old_product_img" value="<?= htmlspecialchars($product['product_img']); ?>">
				<div>
					<label for="product_title">Product Name:</label>
						<input type="text" id="product_title" name="product_title" value="<?php echo htmlspecialchars($product['product_title']); ?>" required>
				</div>
				<div>
						<label for="product_desc">Product Description:</label>
							<input type="text" id="product_desc" name="product_desc" value="<?php echo htmlspecialchars($product['product_desc']); ?>" required>
				</div>
				<div>
					<label for="product_price">Product Price:</label>
						<input type="number" id="product_price" name="product_price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['product_price']); ?>" required>
				</div>
				<div>
					<label for="quantity">Stock Count:</label>
						<input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
				</div>
				<div>
					<label for="category">Category:</label>
						<select name="product_cat" id="category" required>
							<option value="" disabled selected>Select Category</option>
							<?php while ($row = $categoryResult->fetch_assoc()) : ?>
							<option value="<?= $row['id']; ?>" <?= ($row['id'] == $product['product_cat']) ? 'selected' : ''; ?>>
							<?= $row['cat_title']; ?>
							</option>
							<?php endwhile; ?>
						</select>
				</div>
				<div>
					<label for="brand"> Brand: </label>
						<select name="product_brand" id="brand" required>
							<option value="" disabled selected>Select Brand</option>
							<?php while ($row = $brandResult->fetch_assoc()) : ?>
							<option value="<?= $row['id']; ?>" <?= ($row['id'] == $product['product_brand']) ? 'selected' : ''; ?>>
							<?= $row['brand_title']; ?>
							</option>
							<?php endwhile; ?>
						</select>
				</div>
					<div>
					<label for="product_img">Product Image:</label>
					<input type="file" id="product_img" name="product_img">
				</div>
					
					<input class="submit" type="submit"  value="Update Product">

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

