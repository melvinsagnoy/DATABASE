<?php
// Start session
session_start();

// Include database connection file
include_once 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_username'])) {
    // Redirect to login page if admin is not logged in
    header("Location: admin_login.php");
    exit();
}

// Retrieve admin details from the database based on session data
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $sql = "SELECT * FROM admins WHERE AdminID = ?";
} elseif (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    $sql = "SELECT * FROM admins WHERE Username = ?";
}

$stmt = $conn->prepare($sql);
if (isset($admin_id)) {
    $stmt->bind_param("i", $admin_id);
} elseif (isset($username)) {
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$result_admin = $stmt->get_result();

// Check if admin exists
if ($result_admin->num_rows > 0) {
    // Fetch admin details
    $admin = $result_admin->fetch_assoc();
} else {
    // Redirect to login page if admin does not exist
    header("Location: admin_login.php");
    exit();
}

// Fetch orders with customer name, order date, and product information
$sql_orders = "SELECT Orders.OrderID, Customers.Name AS CustomerName, Orders.OrderDate, Products.Name AS ProductName, OrderDetails.Quantity, OrderDetails.UnitPrice, Orders.TotalAmount, Orders.Status 
        FROM Orders 
        INNER JOIN Customers ON Orders.CustomerID = Customers.CustomerID
        INNER JOIN OrderDetails ON Orders.OrderID = OrderDetails.OrderID
        INNER JOIN Products ON OrderDetails.ProductID = Products.ProductID";


$result_orders = $conn->query($sql_orders);

// Check if there was an error with the query
if ($result_orders === false) {
    echo "Error executing query: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">

<!-- Sidebar -->
<aside class="fixed top-0 left-0 h-screen w-64 bg-white shadow-lg">
    <div class="p-6">
        <img src="path/to/admin-avatar.png" alt="Admin Avatar" class="w-24 h-24 rounded-full mx-auto">
        <h2 class="text-xl font-semibold mt-4 text-gray-800">Admin Dashboard</h2>
        <nav class="mt-6">
            <a href="#" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-blue-500 text-white text-sm font-medium no-underline hover:bg-blue-600 hover:text-white">Dashboard</a>
            <a href= "add_product.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Products</a>
            <a href="order.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Orders</a>
            <a href="customer.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Customers</a>
        </nav>
    </div>
</aside>

<!-- Main Content -->
<main class="ml-64 p-6">
    <div class="container mt-5">
        <h2>Order Details</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Product Name</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are any orders
            if ($result_orders->num_rows > 0) {
                // Output data of each row
                while ($row = $result_orders->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["OrderID"] . "</td>";
                    echo "<td>" . $row["CustomerName"] . "</td>";
                    echo "<td>" . $row["OrderDate"] . "</td>";
                    echo "<td>" . $row["ProductName"] . "</td>";
                    echo "<td>$" . $row["TotalAmount"] . "</td>";
                    echo "<td>" . $row["Status"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No orders found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
