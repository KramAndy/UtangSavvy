<?php
require 'auth.php';
require 'config.php';
require 'fpdf/fpdf.php';  // Make sure the fpdf folder is correctly placed

$table = $_GET['filter'] ?? 'products';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$sql = "";

if ($table === 'products') {
    $sql = "SELECT 
            p.id, 
            p.product_title,
            c.cat_title AS category_name,
            b.brand_title AS brand_name,
            p.product_price, 
            DATE_FORMAT(p.created_at, '%M %d, %Y') AS created_at
            FROM products p
            JOIN category c ON p.product_cat = c.id
            JOIN brands b ON p.product_brand = b.id
            WHERE 1";
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND DATE(p.created_at) BETWEEN ? AND ?";
    }
} elseif ($table === 'brands') {
    $sql = "SELECT id, brand_title, DATE_FORMAT(created_at, '%M %d, %Y') AS created_at
            FROM brands";
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " WHERE DATE(created_at) BETWEEN ? AND ?";
    }
} elseif ($table === 'category') {
    $sql = "SELECT id, cat_title, DATE_FORMAT(created_at, '%M %d, %Y') AS created_at
            FROM category";
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " WHERE DATE(created_at) BETWEEN ? AND ?";
    }
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}
if (!empty($start_date) && !empty($end_date)) {
    $stmt->bind_param('ss', $start_date, $end_date);
}
$stmt->execute();
$result = $stmt->get_result();

$total_entries = 0;
$total_value = 0.0;


$pdf = new FPDF('L', 'mm', 'A4'); 
$pdf->AddPage();

$pageWidth = $pdf->getPageWidth();
$imageWidth = 30;

$x = ($pageWidth - $imageWidth) / 2;

$y = 40;

$pdf->SetY($y);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Inventory Management System', 0, 1, 'C');
$pdf->Cell(0, 10, 'UtangSavvy', 0, 1, 'C');
$pdf->Cell(0, 10, 'Report', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$dateRange = (!empty($start_date) && !empty($end_date)) ? "$start_date to $end_date" : "All time";
$pdf->Cell(0, 10, 'Date Range: ' . $dateRange, 0, 1, 'L');
$pdf->Ln(5);

// -----------------------------
// Table Header Setup
// -----------------------------
$w = array(15, 60, 40, 40, 30, 40);





if ($table === 'products') {
    // Define column widths for products table (adjust as needed)
    $w = array(30, 50, 40, 40, 30, 40);
    $headers = array('Product ID', 'Product Title', 'Category', 'Brand', 'Price', 'Date Added');
} elseif ($table === 'brands') {
    $w = array(30, 80, 40);
    $headers = array('Brand ID', 'Brand Title', 'Date Added');
} elseif ($table === 'category') {
    $w = array(30, 80, 40);
    $headers = array('Category ID', 'Category Title', 'Date Added');
}
$pdf->SetFont('Arial', 'B', 12);
$totalWidth = array_sum($w);
$pdf->SetX( (297 - $totalWidth) / 2 );

for ($i = 0; $i < count($headers); $i++) {
    $pdf->Cell($w[$i], 10, $headers[$i], 1, 0, 'C');
}
$pdf->Ln();



$pdf->SetFont('Arial', '', 12);

while ($row = $result->fetch_assoc()) {
	 $pdf->SetX( (297 - $totalWidth) / 2 );

    if ($table === 'products') {
        $pdf->Cell($w[0], 10, $row['id'], 1, 0, 'C');
        $pdf->Cell($w[1], 10, $row['product_title'], 1, 0, 'C');
        $pdf->Cell($w[2], 10, $row['category_name'], 1, 0, 'C');
        $pdf->Cell($w[3], 10, $row['brand_name'], 1, 0, 'C');
        $pdf->Cell($w[4], 10, number_format($row['product_price'], 2), 1, 0, 'C');
        $pdf->Cell($w[5], 10, $row['created_at'], 1, 0, 'C');
        $pdf->Ln();
        $total_value += $row['product_price'];
    } elseif ($table === 'brands') {
        $pdf->Cell($w[0], 10, $row['id'], 1, 0, 'C');
        $pdf->Cell($w[1], 10, $row['brand_title'], 1, 0, 'C');
        $pdf->Cell($w[2], 10, $row['created_at'], 1, 0, 'C');
        $pdf->Ln();
    } elseif ($table === 'category') {
        $pdf->Cell($w[0], 10, $row['id'], 1, 0, 'C');
        $pdf->Cell($w[1], 10, $row['cat_title'], 1, 0, 'C');
        $pdf->Cell($w[2], 10, $row['created_at'], 1, 0, 'C');
        $pdf->Ln();
    }
    $total_entries++;
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Summary', 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Total Entries: ' . $total_entries, 0, 1, 'L');
if ($table === 'products') {
    $pdf->Cell(0, 10, 'Total Value: $' . number_format($total_value, 2), 0, 1, 'L');
} else {
    $pdf->Cell(0, 10, 'Total Value: N/A', 0, 1, 'L');
}

$pdf->Output('I', 'report.pdf');
exit;
?>
