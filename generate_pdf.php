<?php
// Include TCPDF library
require_once('C:\Users\MSI ULTRA GAMING\Desktop\DATABASE\TCPDF-main\tcpdf.php');

// Include database connection file
include_once 'db_connection.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['customer_id']) && !isset($_SESSION['username'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}

// Retrieve cart items for the logged-in user from the database
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $sql = "SELECT ShoppingCart.CartID, Products.Name AS ProductName, Products.Description, Products.Price, ShoppingCart.Quantity
            FROM ShoppingCart
            JOIN Products ON ShoppingCart.ProductID = Products.ProductID
            WHERE ShoppingCart.CustomerID = ?";
} elseif (isset($_SESSION['username'])) {
    // If using username instead of customer_id
    $username = $_SESSION['username'];
    $sql = "SELECT ShoppingCart.CartID, Products.Name AS ProductName, Products.Description, Products.Price, ShoppingCart.Quantity
            FROM ShoppingCart
            JOIN Products ON ShoppingCart.ProductID = Products.ProductID
            JOIN Customers ON ShoppingCart.CustomerID = Customers.CustomerID
            WHERE Customers.Username = ?";
}

$stmt = $conn->prepare($sql);
if (isset($customer_id)) {
    $stmt->bind_param("i", $customer_id);
} elseif (isset($username)) {
    $stmt->bind_param("s", $username);
}

$stmt->execute();
$result = $stmt->get_result();

// Initialize PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Your Name');
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Receipt');
$pdf->SetSubject('Receipt');
$pdf->SetKeywords('Receipt, PDF');

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add content to PDF
$html = '
<h1>Receipt</h1>
<table border="1">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>';

$total_price = 0;
while ($row = $result->fetch_assoc()) {
    $total = $row['Price'] * $row['Quantity'];
    $total_price += $total;
    $html .= '
        <tr>
            <td>' . $row['ProductName'] . '</td>
            <td>' . $row['Description'] . '</td>
            <td>$' . $row['Price'] . '</td>
            <td>' . $row['Quantity'] . '</td>
            <td>$' . number_format($total, 2) . '</td>
        </tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><strong>Total Price:</strong></td>
            <td>$' . number_format($total_price, 2) . '</td>
        </tr>
    </tfoot>
</table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('receipt.pdf', 'I');

// Close the database connection
$conn->close();
?>
