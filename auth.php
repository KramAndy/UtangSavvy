<?php
session_start();
if (!isset($_SESSION['id'])) {
     header("Location: index.php?error=not_logged_in");
    die();
}
if(isset($_SESSION['message'])){
	echo $_SESSION['message'];
	unset($_SESSION['message']);
}
?>