<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	$sql = "SELECT 
						p.id, 
						p.product_title, 
						p.product_desc, 
						p.product_price,
						p.quantity,
						p.product_img,
						p.product_keywords,
						DATE_FORMAT(p.created_at, '%M %d, %Y') AS created_at,
						c.cat_title AS category_name,
						b.brand_title AS brand_name
					FROM products p
					JOIN category c ON p.product_cat = c.id
					JOIN brands b ON p.product_brand = b.id";

				
				$result = $conn->query($sql); 
				if($result->num_rows > 0) :
	
$search = "";
$search_query = "";
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>UtangSavvy</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="header_footer_style.css">
		<link rel="stylesheet" href="inventory.css">
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

				<div class="head"><h1>Product List</h1></div>
				
				
	</header>
	<main>
			<div class="search-container">
				<div class="add-item">
				<a href="add_item.php" class="btn">
						<p> Add Product</p>
					</a>
				</div>
				<div class=search>
					<label for="searchBpx"> Search</label>
					<input type="text" id="searchInput" name="searchBox" placeholder="Search items...">
				</div>
			</div>
		<div class="table-section">
					<table>
					<thead>
						<tr>
							<th>Product ID</th>
							<th>Image</th>
							<th>Category</th>
							<th>Product Name</th>
							<th>Brand</th>
							<th>Description</th>
							<th>Price</th>
							<th>Stock</th>
							<th>Date Added</th>
							<th></th>
						</tr>
					</thead>
						<tbody id="inventory-body">
						<?php while($row = $result->fetch_assoc()) : ?>
							<tr>
								<td><?= htmlspecialchars($row['id']) ?></td>
								<td><img src="<?= htmlspecialchars($row["product_img"]) ?>" style='width:50px;height:50px;'></td>
								<td><?= htmlspecialchars($row['category_name']) ?></td>
								<td><?= htmlspecialchars($row['product_title']) ?></td>
								<td><?= htmlspecialchars($row['brand_name']) ?></td>
								<td class="desc-col"><div class="desc"><?= htmlspecialchars($row['product_desc']) ?></div></td>
								<td>&#8369;<?= htmlspecialchars($row['product_price']) ?></td>
								<td><?= htmlspecialchars($row['quantity']) ?></td>
								<td><?= htmlspecialchars($row['created_at']) ?></td>
								<td>
								  <a href="edit_product.php?id=<?=htmlspecialchars($row['id']) ?>" class="desktop-only">Edit</a>
								  <a href="delete.php?table=products&id=<?= htmlspecialchars($row['id']) ?>" class="desktop-only" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
								</td>

								
							</tr>
						<?php endwhile;?>
					</tbody>
				<?php else:?>
					
					<p>No Records found</p>
					<a href="add_item.php" class="btn1">
					<div class="add-item-bottom">
						<p> Add Product</p>
					</div>
					</a>
				<?php endif; ?>
				</table>
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
$("#searchInput").on("keyup", function() {
    let query = $(this).val();
    $.ajax({
        url: "search_inventory.php",
        type: "GET",
        data: { q: query },
        success: function(data) {
            $("#inventory-body").html(data);
        }
    });
});
</script>
<?php showToastr(); ?>
	</body>
	
</html>