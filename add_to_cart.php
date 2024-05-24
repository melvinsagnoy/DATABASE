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

// Check if the form is submitted
if (isset($_POST['add_to_cart'])) {
    // Get the customer ID from session
    $customer_id = $_SESSION['user_id'];
    // Get the product ID from form submission
    $product_id = $_POST['product_id'];
    // Set the quantity (this could also be a form input)
    $quantity = 1;

    // Prepare and execute the insertion query
    $sql_insert = "INSERT INTO ShoppingCart (CustomerID, ProductID, Quantity) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $customer_id, $product_id, $quantity);

    if ($stmt_insert->execute()) {
        // Redirect to the cart page or show a success message
        header("Location: cart.php");
        exit();
    } else {
        // Handle error
        echo "Error: " . $stmt_insert->error;
    }

    // Close the statement
    $stmt_insert->close();
}

// Close database connection
$conn->close();
?>
