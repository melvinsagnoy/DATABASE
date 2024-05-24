<?php
// Include database connection file
include_once 'db_connection.php';

// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_username'])) {
    // Redirect to login page if admin is not logged in
    header("Location: admin_login.php");
    exit();
}

// Check if form is submitted and product ID is provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productID'])) {
    // Retrieve product ID from form
    $productID = $_POST['productID'];

    // Retrieve form data
    $productName = $_POST['productName'];
    $productDescription = $_POST['productDescription'];
    $productPrice = $_POST['productPrice'];
    $productQuantity = $_POST['productQuantity'];
    $categoryID = $_POST['category'];
    $manufacturerID = $_POST['manufacturer'];
    $isPrescriptionRequired = isset($_POST['isPrescriptionRequired']) ? 1 : 0;

    // Update product in database
    $sql_update_product = "UPDATE Products SET 
                            Name = '$productName',
                            Description = '$productDescription',
                            Price = '$productPrice',
                            Quantity = '$productQuantity',
                            CategoryID = '$categoryID',
                            ManufacturerID = '$manufacturerID',
                            IsPrescriptionRequired = '$isPrescriptionRequired'
                            WHERE ProductID = '$productID'";

    if ($conn->query($sql_update_product) === TRUE) {
        // Redirect to products page after successful update
        header("Location: products.php");
        exit();
    } else {
        echo "Error updating product: " . $conn->error;
    }
} else {
    // Redirect to products page if form is not submitted or product ID is not provided
    header("Location: products.php");
    exit();
}
?>
