<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

$debtor_id = (int)($_POST['debtor_id'] ?? 0);

if (!$debtor_id) {
    header("Location: ledger.php");
    exit();
}

// Fetch debtor's tier
$stmt = $conn->prepare("SELECT tier FROM debtors WHERE id = ?");
$stmt->bind_param("i", $debtor_id);
$stmt->execute();
$debtor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$debtor) {
    setToastr('error', 'Debtor not found.', 'Error!');
    header("Location: ledger.php");
    exit();
}

$tier = $debtor['tier'] ?? 'Unassigned';

// Define tier limits
$limits = [
    'Bronze' => 2000,
    'Silver' => 5000,
    'Gold' => 10000,
    'Platinum' => 20000,
    'Unassigned' => 2000
];
$limit = $limits[$tier];

// Find or create open ledger book
$stmt = $conn->prepare("SELECT id FROM ledger_books WHERE debtor_id = ? AND status = 'open' LIMIT 1");
$stmt->bind_param("i", $debtor_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    $stmt = $conn->prepare("INSERT INTO ledger_books (debtor_id, status) VALUES (?, 'open')");
    $stmt->bind_param("i", $debtor_id);
    $stmt->execute();
    $book_id = $stmt->insert_id;
    $stmt->close();
} else {
    $book_id = $book['id'];
}

// Calculate current total
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM ledger_entries WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get submitted items and cash
$items = $_POST['items'] ?? [];
$cash_amount = floatval($_POST['cash_amount'] ?? 0);

// Validate input: require at least one product or cash
if (empty($items) && $cash_amount <= 0) {
    setToastr('error', 'Please select at least one product or enter a cash amount.', 'No Input!');
    header("Location: view_ledger.php?debtor_id=" . $debtor_id);
    exit();
}

// Calculate total of new items
$total_new_items = 0;
foreach ($items as $item) {
    $price = floatval($item['price'] ?? 0);
    if ($price > 0) $total_new_items += $price;
}

// Total after adding products + cash
$new_total = $total + $total_new_items + $cash_amount;

if ($new_total > $limit) {
    $remaining = $limit - $total;
    setToastr(
        'error',
        'Adding selected products/cash (₱' . number_format($total_new_items + $cash_amount,2) . 
        ') exceeds the limit for ' . $tier . ' tier (₱' . number_format($limit,2) . 
        '). Remaining credit: ₱' . number_format(max($remaining,0),2),
        'Credit Limit Exceeded'
    );
    header("Location: view_ledger.php?debtor_id=" . $debtor_id);
    exit();
}

// Insert products
foreach ($items as $id => $item) {
    $product_id = intval($id); // get product ID from array key
    $title = trim($item['title'] ?? '');
    $price = floatval($item['price'] ?? 0);
    if (!$title || $price <= 0) continue;

    $stmt = $conn->prepare("INSERT INTO ledger_entries (book_id, product_id, description, amount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $book_id, $product_id, $title, $price);
    $stmt->execute();
    $stmt->close();
}

// Insert cash if provided
if ($cash_amount > 0) {
    $stmt = $conn->prepare("INSERT INTO ledger_entries (book_id, description, amount) VALUES (?, 'Cash', ?)");
    $stmt->bind_param("id", $book_id, $cash_amount);
    $stmt->execute();
    $stmt->close();
}

header("Location: view_ledger.php?debtor_id=" . $debtor_id);
exit();
