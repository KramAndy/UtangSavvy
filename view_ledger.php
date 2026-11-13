<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

if (!isset($_GET['debtor_id'])) {
    die("No debtor selected.");
}

$debtor_id = (int)$_GET['debtor_id'];

// Fetch debtor
$stmt = $conn->prepare("SELECT name, tier FROM debtors WHERE id = ?");
$stmt->bind_param("i", $debtor_id);
$stmt->execute();
$debtor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$debtor) {
    die("Debtor not found.");
}

// Fetch open book (if exists)
$stmt = $conn->prepare("SELECT id FROM ledger_books WHERE debtor_id = ? AND status = 'open' LIMIT 1");
$stmt->bind_param("i", $debtor_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total = 0;
$entries = [];

if ($book) {
    $book_id = $book['id'];

    // Fetch entries for open book
    $stmt = $conn->prepare("SELECT description, amount, created_at 
                            FROM ledger_entries 
                            WHERE book_id = ? 
                            ORDER BY created_at ASC");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $entries = $stmt->get_result();
    $stmt->close();
}

// Fetch closed books for history
$stmt = $conn->prepare("SELECT id, created_at, closed_at 
                        FROM ledger_books 
                        WHERE debtor_id = ? AND status = 'closed' 
                        ORDER BY closed_at DESC");
$stmt->bind_param("i", $debtor_id);
$stmt->execute();
$closed_books = $stmt->get_result();
$stmt->close();
$name = $debtor['name'];
$shortName = (mb_strlen($name) > 5) ? mb_substr($name, 0, 5) . '...' : $name;

$tier = $debtor['tier'] ?? 'Unassigned';
$limits = [
    'Bronze'   => 2000,
    'Silver'   => 5000,
    'Gold'     => 10000,
    'Platinum' => 20000,
    'Unassigned' => 2000
];
$limit = $limits[$tier];

// Calculate total credit in open book
if ($book) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM ledger_entries WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $total_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $current_total = $total_row['total'];
} else {
    $current_total = 0;
}

$remaining = max($limit - $current_total, 0);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($debtor['name']) ?>'s Ledger</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="header_footer_style.css">
	<link rel="stylesheet" href="view_ledger.css">
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
		<div class="head"><h2><?= htmlspecialchars($shortName) ?>'s Store Credits</h2></div>
	</header>
		<main>
	<div class="wrapper">
		<p>• Add items credited or cash borrowed.</p>
		<div class="tier-info">
		<p><strong>Tier:</strong> <?= htmlspecialchars($tier) ?></p>
		<p><strong>Credit Limit:</strong> ₱<?= number_format($limit, 2) ?></p>
		<p><strong>Remaining Credit:</strong> ₱<?= number_format($remaining, 2) ?></p>
	</div>

		<div class="formdiv">	
    <form method="POST" action="process_ledger.php" id="ledgerForm">
        <input type="hidden" name="debtor_id" value="<?= $debtor_id ?>">
		
		 <div style="margin-bottom:15px;">
            <label for="cashAmount"><strong>Cash Amount (Optional):</strong></label>
            <input type="number" step="0.01" name="cash_amount" id="cashAmount" placeholder="Enter cash amount">
        </div>
        <!-- Selected Products Table -->
        <h4>Selected Products</h4>
        <table id="selectedProducts" border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dynamically filled -->
            </tbody>
			<tfoot>
				<tr>
					<th>Total</th>
					<th id="selectedTotal">₱0.00</th>
					<th></th>
				</tr>
			</tfoot>
        </table>
		
        <button type="button" id="pickProductBtn">Pick Products from Inventory</button>
        <br><br>
        <button type="submit" class="button">Add Selected Products to Ledger</button>
    </form>
</div>

<!-- Product Selection Modal -->
<div id="productModal" class="productModal">
    <h3>Select Products</h3>

    <!-- Live Search -->
    <input type="text" id="productSearch" placeholder="Search products..." style="margin-bottom:10px; width:100%; padding:5px;">
	<div class="product-table-wrapper">
    <table id="productTable" border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Select</th>
            </tr>
        </thead>
        <tbody id="addProductTable">
            <?php
            $product_sql = "SELECT id, product_title, product_price, quantity FROM products";
            $product_result = $conn->query($product_sql);
            while($prod = $product_result->fetch_assoc()):
            ?>
            <tr>
                <td class="prodTitle"><?= htmlspecialchars($prod['product_title']) ?></td>
                <td><?= number_format($prod['product_price'], 2) ?></td>
                <td>
                    <button type="button" class="addProductBtn" 
                        data-id="<?= $prod['id'] ?>" 
                        data-title="<?= htmlspecialchars($prod['product_title']) ?>" 
                        data-price="<?= $prod['product_price'] ?>"
						data-qty="<?= $prod['quantity'] ?>"
						<?= $prod['quantity'] <= 0 ? 'disabled' : '' ?>
						>
						<?= $prod['quantity'] <= 0 ? 'Out of stock' : 'Add' ?>
                       
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
	</div>
    <button class="pickProductBtn" onclick="closeModal()">Close</button>
</div>

<script>
    // Live search
    $('#productSearch').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('#addProductTable tr').filter(function() {
            $(this).toggle($(this).find('.prodTitle').text().toLowerCase().indexOf(val) > -1);
        });
    });
</script>


	</div>
	<div class="debtors_table">
		<h3>Current Credit</h3>
			<div class="table-section">
				<?php if ($book): ?>
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
					
					</table>
					</tbody>
					<h3>Total: ₱<?= number_format($total, 2) ?></h3>

					<form method="POST" action="close_book.php">
						<input type="hidden" name="book_id" value="<?= $book_id ?>">
						<input type="hidden" name="debtor_id" value="<?= $debtor_id ?>">
						<button type="submit" class="button">Mark As Paid</button>
					</form>
				<?php else: ?>
					<p><em>No active ledger. Start by adding an item (a new book will be created automatically).</em></p>
				<?php endif; ?>
			</div>
	</div>
	<div class="debtors_table">
		<h3>Previous Store Credits</h3>
			<div class="table-section">
				<?php if ($closed_books->num_rows > 0): ?>
					<table>
					<thead>
						<tr>
							<th>Started on</th>
							<th>Paid on</th>
							<th>Action</th>
						</tr>
					</thead>
						<tbody>
						<?php while ($closed = $closed_books->fetch_assoc()): ?>
							<tr>
								<td><?= date("M j, Y", strtotime($closed['created_at'])) ?></td>
								<td><?= $closed['closed_at'] ? date("M j, Y", strtotime($closed['closed_at'])) : '-' ?></td>
								<td><a href="view_closed_book.php?book_id=<?= $closed['id'] ?>">View Details</a></td>
							</tr>
						<?php endwhile; ?>
					</table>
					</tbody>
				<?php else: ?>
					<p><em>This costumer has no credit history.</em></p>
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
let selectedProducts = [];

// Open modal
$('#pickProductBtn').click(function(){
    $('#productModal').show();
});

function closeModal() {
    $('#productModal').hide();
}

// Add product from modal to selectedProducts
$('.addProductBtn').click(function(){
    const id = $(this).data('id');
    const title = $(this).data('title');
    const price = $(this).data('price');

    // Avoid duplicates
    if(selectedProducts.find(p => p.id == id)) return;

    selectedProducts.push({id, title, price});
    renderSelectedProducts();
});

// Remove product
function removeProduct(id){
    selectedProducts = selectedProducts.filter(p => p.id != id);
    renderSelectedProducts();
}

// Render selected products table and hidden inputs
function renderSelectedProducts(){
    const tbody = $('#selectedProducts tbody');
    tbody.empty();

    let total = 0;

    selectedProducts.forEach(p => {
        total += parseFloat(p.price);

        tbody.append(`
            <tr>
                <td>${p.title}</td>
                <td>₱${Number(p.price).toFixed(2)}</td>
                <td>
                    <button type="button" onclick="removeProduct(${p.id})">
                        Remove
                    </button>
                </td>
            </tr>
            <input type="hidden" name="items[${p.id}][title]" value="${p.title}">
            <input type="hidden" name="items[${p.id}][price]" value="${p.price}">
        `);
    });

    updateGrandTotal(total);
}
function updateGrandTotal(productsTotal){
    const cash = parseFloat($('#cashAmount').val()) || 0;
    const grandTotal = productsTotal + cash;

    $('#selectedTotal').text('₱' + grandTotal.toFixed(2));
}
$('#cashAmount').on('input', function(){
    let productsTotal = 0;

    selectedProducts.forEach(p => {
        productsTotal += parseFloat(p.price);
    });

    updateGrandTotal(productsTotal);
});


// Submit form: will send items array to process_ledger.php
$('#ledgerForm').submit(function(){
    const cash = parseFloat($('#cashAmount').val()) || 0;

    if(selectedProducts.length === 0 && cash <= 0){
        alert("Please select a product or enter a cash amount.");
        return false;
    }

    return true;
});

</script>

		<?php showToastr(); ?>
</body>
</html>
