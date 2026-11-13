<?php
require 'auth.php';
include 'config.php';

$table = $_GET['filter'] ?? 'products'; 
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$tableHTML = "";
$params = [];
$types = "";

// Build SQL based on filter
if ($table === 'products') {
    $sql = "SELECT 
            p.id,
            p.product_title,
            c.cat_title AS category_name,
            b.brand_title AS brand_name,
            p.product_price,
            COUNT(le.id) AS transaction_count,
            DATE_FORMAT(p.created_at, '%M %d, %Y') AS created_at
        FROM products p
        JOIN category c ON p.product_cat = c.id
        JOIN brands b ON p.product_brand = b.id
        LEFT JOIN ledger_entries le 
            ON le.product_id = p.id
        LEFT JOIN ledger_books lb 
            ON le.book_id = lb.id
        ";

$conditions = [];
if (!empty($start_date) && !empty($end_date)) {
    $conditions[] = "DATE(le.created_at) BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
    $types = "ss";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY p.id";
} elseif ($table === 'brands') {
    $sql = "SELECT id, brand_title, DATE_FORMAT(created_at, '%M %d, %Y') AS created_at 
            FROM brands";
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$start_date, $end_date];
        $types = "ss";
    }
} elseif ($table === 'category') {
    $sql = "SELECT id, cat_title, DATE_FORMAT(created_at, '%M %d, %Y') AS created_at 
            FROM category";
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$start_date, $end_date];
        $types = "ss";
    }
}

// Prepare statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("MySQL prepare failed: " . $conn->error);
}

// Bind parameters if needed
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute and get results
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $tableHTML .= "<table class=\"table\">";
    $tableHTML .= "<tr>";
    if ($table === 'products') {
        $tableHTML .= "<th>Product ID</th>";
        $tableHTML .= "<th>Product Title</th>";
        $tableHTML .= "<th>Category</th>";
        $tableHTML .= "<th>Brand</th>";
        $tableHTML .= "<th>Price</th>";
        $tableHTML .= "<th>Sold/Credited</th>";
        $tableHTML .= "<th>Date Added</th>";
    } elseif ($table === 'brands') {
        $tableHTML .= "<th>Brand ID</th>";
        $tableHTML .= "<th>Brand Name</th>";
        $tableHTML .= "<th>Created At</th>";
    } elseif ($table === 'category') {
        $tableHTML .= "<th>Category ID</th>";
        $tableHTML .= "<th>Category Name</th>";
        $tableHTML .= "<th>Created At</th>";
    }
    $tableHTML .= "</tr>";

    while ($row = $result->fetch_assoc()) {
        $tableHTML .= "<tr>";
        foreach ($row as $key => $cell) {
            if (is_string($cell) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $cell)) {
                $tableHTML .= "<td><img src='{$cell}' width='50'></td>";
            } else {
                $tableHTML .= "<td>{$cell}</td>";
            }
        }
        $tableHTML .= "</tr>";
    }

    $tableHTML .= "</table>";
} else {
    $tableHTML .= "No records found.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>UtangSavvy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="header_footer_style.css">
    <link rel="stylesheet" href="report.css">
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
    <div class="head"><h1>TRANSACTION HISTORY</h1></div>
</header>
<main>
    <div class="wrapper">
        <div class="filter">
            <form method="GET" action="">
                <div>
                    <label for="filter">Filter By:</label>
                    <select name="filter" id="filter" onchange="this.form.submit()">
                        <option value="products" <?= ($table === 'products') ? 'selected' : '' ?>>Products</option>
                        <option value="brands" <?= ($table === 'brands') ? 'selected' : '' ?>>Brands</option>
                        <option value="category" <?= ($table === 'category') ? 'selected' : '' ?>>Category</option>
                    </select>
                </div>
                <div>
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div>
                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <button type="submit" class="button">Apply Filter</button>
            </form>
        </div>
       <!-- <div class="export">
            <form method="GET" action="export_pdf.php" target="_blank">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($table) ?>">
                <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                <button type="submit" class="button2">Export to PDF</button>
            </form>
        </div>-->
			<div class="search-box">
				<label for="liveSearch">Search</label>
				<input type="text" id="liveSearch" placeholder="Search..." onkeyup="liveSearchTable()">
			</div>
    </div>
    <div class="table-section">
        <?= $tableHTML ?>
    </div>
</main>
<footer>
    <p>© UtangSavvy.com v1.0 All rights reserved (2025)</p>
</footer>
<script>
function toggleSidebar() {
    document.querySelector("nav").classList.toggle("active");
}
function redirectPage(select) {
    const val = select.value;
    if(val && val !== " ") window.location.href = val;
}
function liveSearchTable() {
    const input = document.getElementById("liveSearch");
    const filter = input.value.toLowerCase();
    const table = document.querySelector(".table");
    
    if (!table) return;

    const rows = table.getElementsByTagName("tr");

    // Skip header row (i = 1)
    for (let i = 1; i < rows.length; i++) {
        let rowText = rows[i].textContent.toLowerCase();
        rows[i].style.display = rowText.includes(filter) ? "" : "none";
    }
}
</script>


</div>
</body>
</html>
