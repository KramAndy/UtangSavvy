<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

if (!isset($_GET['book_id'])) {
    die("No ledger book selected.");
}

$book_id = (int)$_GET['book_id'];

// Fetch book details and debtor name
$stmt = $conn->prepare("
    SELECT lb.id, lb.created_at, lb.closed_at, d.name AS debtor_name
    FROM ledger_books lb
    JOIN debtors d ON lb.debtor_id = d.id
    WHERE lb.id = ?
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    die("Ledger book not found.");
}

// Fetch entries
$stmt = $conn->prepare("
    SELECT description, amount, created_at
    FROM ledger_entries
    WHERE book_id = ?
    ORDER BY created_at ASC
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$entries = $stmt->get_result();
$stmt->close();

$total = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['debtor_name']) ?>'s Old Credits</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="header_footer_style.css">
	<link rel="stylesheet" href="view_closed_book.css">	
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
		<div class="head"><h2><?= htmlspecialchars($book['debtor_name']) ?>'s Credit History</h2></div>
	</header>
		<main>
			<div class="cont">
				
				<p><strong>Started on:</strong> <?= date("M j, Y", strtotime($book['created_at'])) ?></p>
				<p><strong>Paid on:</strong> <?= date("M j, Y", strtotime($book['closed_at'])) ?></p>
				<div class="table-section">
				<?php if ($entries->num_rows > 0): ?>
					<table>
					<thead>
						<tr>
							<th>Description</th>
							<th>Price</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($row = $entries->fetch_assoc()): ?>
							<?php $total += $row['amount']; ?>
							<tr>
								<td><?= htmlspecialchars($row['description']) ?></td>
								<td>₱<?= number_format($row['amount'], 2) ?></td>
								<td><?= date("M j, Y", strtotime($row['created_at'])) ?></td>
							</tr>
						<?php endwhile; ?>
					</tbody>
					</table>

					<h3>Total: ₱<?= number_format($total, 2) ?></h3>
				<?php else: ?>
					<p><em>No entries found for this ledger book.</em></p>
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
		<?php showToastr(); ?>
</body>
</html>
