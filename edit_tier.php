<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

if (!isset($_POST['debtor_id'], $_POST['tier'])) {
    setToastr('error', 'Invalid request.', 'Error!');
    header("Location: ledger.php");
    exit();
}

$debtor_id = (int)$_POST['debtor_id'];
$tier = trim($_POST['tier']);

// Validate tier
$valid_tiers = ['Bronze', 'Silver', 'Gold', 'Platinum'];
if (!in_array($tier, $valid_tiers)) {
    setToastr('error', 'Invalid tier selection.', 'Error!');
    header("Location: ledger.php");
    exit();
}

// Update tier
$stmt = $conn->prepare("UPDATE debtors SET tier = ? WHERE id = ?");
$stmt->bind_param("si", $tier, $debtor_id);

if ($stmt->execute()) {
    setToastr('success', "Tier updated to $tier successfully.", 'Updated!');
} else {
    setToastr('error', 'Failed to update tier.', 'Database Error');
}
$stmt->close();

// Redirect back
header("Location: view_ledger.php?debtor_id=" . $debtor_id);
exit();
