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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="container mx-auto py-8">
        <h2 class="text-3xl font-semibold mb-4">Checkout</h2>
        <?php
        $total_price = 0;
        $total_quantity = 0;
        while ($row = $result->fetch_assoc()) {
            $total = $row['Price'] * $row['Quantity'];
            $total_price += $total;
            $total_quantity += $row['Quantity'];
        }
        ?>
        <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
            <p class="text-lg font-semibold">Total Price: $<?php echo number_format($total_price, 2); ?></p>
            <p class="text-lg font-semibold">Total Quantity: <?php echo $total_quantity; ?></p>
        </div>
        <form action="process_checkout.php" method="post">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Confirm Order</button>
        </form>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
