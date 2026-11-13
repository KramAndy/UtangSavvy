<?php
include 'auth.php';
include 'config.php';
include 'helper.php';

if (!empty($_POST['name'])) {
    $name    = trim($_POST['name']);
    $contact = trim($_POST['contact']);

    // Validate contact if not empty
    if (!empty($contact) && !preg_match("/^[0-9+\-\(\)\s]+$/", $contact)) {
        setToastr('error', 'Please check contact format.', 'Invalid Format!');
    }

    $name    = $conn->real_escape_string($name);
    $contact = $conn->real_escape_string($contact);

    $conn->query("INSERT INTO debtors (name, contact) VALUES ('$name','$contact')");
}

header("Location: ledger.php");
exit();
