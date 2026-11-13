<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['book_id'])) {
    setToastr("error", "Invalid request.");
    header("Location: ledger.php");
    exit;
}

$book_id = (int)$_POST['book_id'];

// Check if book exists and is still open
$stmt = $conn->prepare("SELECT debtor_id, status FROM ledger_books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$stmt->bind_result($debtor_id, $status);
$stmt->fetch();
$stmt->close();


if (!$status) {
    setToastr("error", "Ledger book not found.");
    header("Location: ledger.php");
    exit;
}

if ($status === 'closed') {
    setToastr("warning", "Ledger book is already closed.");
    header("Location: view_ledger.php?debtor_id=" . $debtor_id);
    exit;
}

// Close the book
$stmt = $conn->prepare("UPDATE ledger_books SET status = 'closed', closed_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $book_id);

if ($stmt->execute()) {
    setToastr("success", "Ledger book marked as paid.");
} else {
    setToastr("error", "Failed to close ledger book.");
}

$stmt->close();
header("Location: view_ledger.php?debtor_id=" . $debtor_id);
exit;
