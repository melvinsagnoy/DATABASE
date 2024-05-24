<?php
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
    $sql = "SELECT ShoppingCart.CartID, ShoppingCart.ProductID, Products.Price, ShoppingCart.Quantity
            FROM ShoppingCart
            JOIN Products ON ShoppingCart.ProductID = Products.ProductID
            WHERE ShoppingCart.CustomerID = ?";
} elseif (isset($_SESSION['username'])) {
    // If using username instead of customer_id
    $username = $_SESSION['username'];
    $sql = "SELECT ShoppingCart.CartID, ShoppingCart.ProductID, Products.Price, ShoppingCart.Quantity
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

// Create a new order
$order_sql = "INSERT INTO Orders (CustomerID, OrderDate, Status) VALUES (?, NOW(), 'Pending')";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $customer_id);
$order_stmt->execute();

if ($order_stmt->error) {
    echo "Error: " . $order_stmt->error;
    exit();
}

$order_id = $order_stmt->insert_id;

// Insert each item from the shopping cart into the OrderDetails table
$order_detail_sql = "INSERT INTO OrderDetails (OrderID, ProductID, Quantity, UnitPrice, Subtotal) VALUES (?, ?, ?, ?, ?)";
$order_detail_stmt = $conn->prepare($order_detail_sql);

$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    $subtotal = $row['Price'] * $row['Quantity'];
    $total_amount += $subtotal; // Accumulate subtotal to calculate total amount
    $order_detail_stmt->bind_param("iiidd", $order_id, $row['ProductID'], $row['Quantity'], $row['Price'], $subtotal);
    $order_detail_stmt->execute();
}

// Update the total amount in the Orders table
$update_total_amount_sql = "UPDATE Orders SET TotalAmount = ? WHERE OrderID = ?";
$update_total_amount_stmt = $conn->prepare($update_total_amount_sql);
$update_total_amount_stmt->bind_param("di", $total_amount, $order_id);
$update_total_amount_stmt->execute();

// Delete items from the shopping cart
$delete_cart_sql = "DELETE FROM ShoppingCart WHERE CustomerID = ?";
$delete_cart_stmt = $conn->prepare($delete_cart_sql);
$delete_cart_stmt->bind_param("i", $customer_id);
$delete_cart_stmt->execute();

// Close all prepared statements
$stmt->close();
$order_stmt->close();
$order_detail_stmt->close();
$update_total_amount_stmt->close();
$delete_cart_stmt->close();

// Redirect to confirmation page or any other appropriate page
header("Location: order_confirmation.php");
exit();

// Close the database connection
$conn->close();
?>