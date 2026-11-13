<?php
	include 'auth.php';
	include 'config.php';
	
	if(isset($_GET['table']) && isset($_GET['id'])){
		 $table = $_GET['table'];
		 $id = $_GET['id'];
		 
		 $allowed_tables = ['users', 'category', 'brands', 'products'];
		 
		 if(!in_array($table, $allowed_tables)){
			die("<div style='background-color: #f44336; color: white; padding: 10px; border-radius: 5px;'>Error: Invalid table name!</div>");
		 }
		 
		 $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
		 $stmt->bind_param("i", $id);
		 
		 if($stmt->execute()){
			  setToastr('success', 'Delete Successful', 'Success!');
		} else {
			$_SESSION['message'] = "<div class=\"notif\">Error: " . $stmt->error . "</div>";
		}
		$stmt->close();
		$conn->close();
		
		header("Location: " . $_SERVER['HTTP_REFERER']);
		exit();

	}
?>
