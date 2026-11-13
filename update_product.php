<?php
	include 'auth.php';
	include 'config.php';
	include 'helper.php';
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	$id = trim($_POST['id']);
	$title = trim($_POST['product_title']);
	$old_product_img = trim($_POST['old_product_img']);
	$desc = trim($_POST['product_desc']);
	$price = trim($_POST['product_price']);
	$quantity = trim($_POST['quantity']);
	$keywords = trim($_POST['product_keywords']);
	$category = trim($_POST['product_cat']);
	$brand = trim($_POST['product_brand']);
	
	$sql = "SELECT * FROM products WHERE product_title = ? AND id != ?";
	$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $title, $id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0){
			 setToastr('error', 'Please choose another product title.', 'Product Title Already Exist!');
			header("Location: " . $_SERVER['HTTP_REFERER']);
			exit();
		}else{
	if (isset($_FILES['product_img']) && $_FILES['product_img']['error'] == 0) {
    $targetDir = "uploads/";
    $photoPath = $targetDir . basename($_FILES['product_img']['name']);
    
    if (!empty($old_product_img) && file_exists($old_product_img)) {
        unlink($old_product_img);
    }

    if (move_uploaded_file($_FILES['product_img']['tmp_name'], $photoPath)) {
        $img = $photoPath; 
    } else {
        setToastr('error', 'Error uploading image.', 'Something Went Wrong!');
        exit;
    }
	} else {
		$img = $old_product_img;
	}

	
		$sql = "UPDATE products SET
				product_title = ?, 
				product_desc = ?, 
				product_price = ?,
				quantity = ?,	
				product_img = ?, 
				product_cat = ?, 
				product_brand = ?
				WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ssdssiii", $title, $desc, $price, $quantity, $img, $category, $brand, $id);
		
		if($stmt->execute()){
		 setToastr('success', 'Product update successful.', 'Success!');
		}else {
		setToastr('error', 'Please try again later.', 'Something Went Wrong!');
		}
		}
	$stmt->close();
	$conn->close();
	
	header("Location: product_inventory.php");
	exit();
}