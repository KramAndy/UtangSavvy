<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

// Fetch all debtors
$stmt = $conn->prepare("SELECT * FROM debtors ORDER BY name ASC");
 $stmt->execute();
 $result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>UtangSavvy | Pautang</title>
	<meta http-eqiv="X-UA-Compatible" content="IE-edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="header_footer_style.css">
	<link rel="stylesheet" href="ledger.css">
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
		<div class="head"><h1>Store Credit Ledger</h1></div>
	</header>
		<main>
			

		<div class="wrapper">
			<h2>Add Borrower</h2>
					<div class="formdiv">
					<form method="POST" action="process_debtor.php">
						<div>
							<label for="name">Name: </label>
							<input type="text" name="name" placeholder="Name" required>
						</div>
						<div>
							<label for="contact"> Contact No.: </label>
							<input type="text" name="contact" placeholder="Contact">
						</div>
					<div>
					  <label for="tier">Tier: </label>
					  <select name="tier" required>
						<option value="Bronze" selected>Bronze (₱2,000 limit)</option>
						<option value="Silver">Silver (₱5,000 limit)</option>
						<option value="Gold">Gold (₱10,000 limit)</option>
						<option value="Platinum">Platinum (₱20,000 limit)</option>
					  </select>
					</div>
						<button type="submit" class="button">Add Borrower</button>
					</form>
					</div>
				</div>
		<div class="debtors_table">
			<div class="search_container">
			<h2> Costumers With Balances</h2>
			<div class=search>
					<label for="searchBpx"> Search</label>
					<input type="text" id="searchInput" name="searchBox" placeholder="Search items...">
				</div>
			</div>
			<div class="table-section">
			<table>
				<thead>
					<tr>
						<th> Name</th>
						<th> Contact Number</th>
						<th> Tier</th>
						<th> Action</th>
						<th> Change Tier</th>
					</tr>
				</thead>
				<tbody id="debtor_body">
					<?php while($row = $result->fetch_assoc()): ?>
					<tr>
					  <td><?= htmlspecialchars($row['name']); ?></td>
					  <td><?= htmlspecialchars($row['contact']); ?></td>
					  <td>
						<?= htmlspecialchars($row['tier']); ?>
					  </td>
					  <td>
						<a class="ledgerButton" href="view_ledger.php?debtor_id=<?= $row['id']; ?>">Open</a>
					  </td>
					  <td>
					  <form method="POST" action="edit_tier.php" style="margin-top:5px;">
						  <input type="hidden" name="debtor_id" value="<?= $row['id']; ?>">
						  <select name="tier" required>
							<option value="Bronze" <?= $row['tier'] == 'Bronze' ? 'selected' : '' ?>>Bronze</option>
							<option value="Silver" <?= $row['tier'] == 'Silver' ? 'selected' : '' ?>>Silver</option>
							<option value="Gold" <?= $row['tier'] == 'Gold' ? 'selected' : '' ?>>Gold</option>
							<option value="Platinum" <?= $row['tier'] == 'Platinum' ? 'selected' : '' ?>>Platinum</option>
						  </select>
						  <button type="submit" class="button1" style="margin-left:5px;">Update</button>
						</form>
					  </td>
					</tr>
					<?php endwhile; ?>
					</tbody>

			</table>
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
			$("#searchInput").on("keyup", function() {
			let query = $(this).val();
			$.ajax({
				url: "search_ledger.php",
				type: "GET",
				data: { q: query },
				success: function(data) {
					$("#debtor_body").html(data);
				}
			});
		});
		</script>
		<?php showToastr(); ?>
</body>

</html>
