<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	$q = isset($_GET['q']) ? trim($_GET['q']) : "";
	$like = "%" . $q . "%";
	
	$sql = "SELECT 
					p.id, 
					p.product_title, 
					p.product_desc, 
					p.product_price, 
					p.product_img,
					p.product_keywords,
					DATE_FORMAT(p.created_at, '%M %d, %Y') AS created_at,
					c.cat_title AS category_name,
					b.brand_title AS brand_name
				FROM products p
			JOIN category c ON p.product_cat = c.id
			JOIN brands b ON p.product_brand = b.id";
			
	if ($q !== ""){
		$sql .= " WHERE product_title LIKE ?
				OR c.cat_title LIKE ?
				OR b.brand_title LIKE ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sss", $like, $like, $like);
		$stmt->execute();
		$result = $stmt->get_result();
	}else{
		$result = $conn->query("SELECT 
					p.id, 
					p.product_title, 
					p.product_desc, 
					p.product_price, 
					p.product_img,
					p.product_keywords,
					DATE_FORMAT(p.created_at, '%M %d, %Y') AS created_at,
					c.cat_title AS category_name,
					b.brand_title AS brand_name
				FROM products p
			JOIN category c ON p.product_cat = c.id
			JOIN brands b ON p.product_brand = b.id");
	}

while($row = $result->fetch_assoc()) :
?>


				<tr>
					<td><?= htmlspecialchars($row['id']) ?></td>
					<td><img src="<?= htmlspecialchars($row["product_img"]) ?>" style='width:50px;height:50px;'></td>
					<td><?= htmlspecialchars($row['category_name']) ?></td>
					<td><?= htmlspecialchars($row['product_title']) ?></td>
					<td><?= htmlspecialchars($row['brand_name']) ?></td>
					<td class="desc-col"><div class="desc"><?= htmlspecialchars($row['product_desc']) ?></div></td>
					<td>&#8369;<?= htmlspecialchars($row['product_price']) ?></td>
					<td><?= htmlspecialchars($row['created_at']) ?></td>
					<td>
					<a href="edit_product.php?id=<?=htmlspecialchars($row['id']) ?>" class="desktop-only">Edit</a>
					<a href="delete.php?table=products&id=<?= htmlspecialchars($row['id']) ?>" class="desktop-only" onclick="return confirm('Are you sure you want to delete this entry?');">Delete</a>
					</td>		
					</tr>
			<?php endwhile; ?>