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

// Retrieve the latest order ID for the logged-in user
$customer_id = $_SESSION['customer_id'] ?? null;
$username = $_SESSION['username'] ?? null;

if ($customer_id) {
    $sql_order = "SELECT OrderID, OrderDate, TotalAmount FROM Orders WHERE CustomerID = ? ORDER BY OrderDate DESC LIMIT 1";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("i", $customer_id);
} else if ($username) {
    $sql_order = "SELECT Orders.OrderID, Orders.OrderDate, Orders.TotalAmount
                  FROM Orders
                  JOIN Customers ON Orders.CustomerID = Customers.CustomerID
                  WHERE Customers.Username = ?
                  ORDER BY Orders.OrderDate DESC LIMIT 1";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("s", $username);
}

$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

$order_id = $order['OrderID'] ?? null;

if ($order_id) {
    $sql_order_details = "SELECT Products.Name, Products.Description, OrderDetails.Quantity, OrderDetails.UnitPrice, OrderDetails.Subtotal
                          FROM OrderDetails
                          JOIN Products ON OrderDetails.ProductID = Products.ProductID
                          WHERE OrderDetails.OrderID = ?";
    $stmt_order_details = $conn->prepare($sql_order_details);
    $stmt_order_details->bind_param("i", $order_id);
    $stmt_order_details->execute();
    $result_order_details = $stmt_order_details->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="container mx-auto py-8">
        <h2 class="text-3xl font-semibold mb-4">Order Confirmation</h2>
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4">
                <p class="text-lg font-semibold mb-2">Your order has been confirmed!</p>
                <p class="text-gray-600">Thank you for your purchase.</p>
                <div class="mt-6">
                    <a href="c_dashboard.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg mr-4">Back to Dashboard</a>
                    <a href="receipt.php" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg">View Receipt</a>
                </div>
            </div>
        </div>
        
        <?php if ($order_id && $result_order_details->num_rows > 0) { ?>
        <div class="bg-white shadow-md rounded-lg overflow-hidden mt-8">
            <div class="p-4">
                <h3 class="text-2xl font-semibold mb-4">Order Details</h3>
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm leading-4 text-gray-700">Product Name</th>
                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm leading-4 text-gray-700">Description</th>
                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm leading-4 text-gray-700">Quantity</th>
                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm leading-4 text-gray-700">Unit Price</th>
                            <th class="py-2 px-4 border-b border-gray-200 text-left text-sm leading-4 text-gray-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_order_details->fetch_assoc()) { ?>
                        <tr>
                            <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['Name']); ?></td>
                            <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['Description']); ?></td>
                            <td class="py-2 px-4 border-b border-gray-200"><?php echo $row['Quantity']; ?></td>
                            <td class="py-2 px-4 border-b border-gray-200">$<?php echo number_format($row['UnitPrice'], 2); ?></td>
                            <td class="py-2 px-4 border-b border-gray-200">$<?php echo number_format($row['Subtotal'], 2); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="mt-4">
                    <p class="text-lg font-semibold">Order Date: <?php echo $order['OrderDate']; ?></p>
                    <p class="text-lg font-semibold">Total Amount: $<?php echo number_format($order['TotalAmount'], 2); ?></p>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</body>
</html>

<?php
$stmt_order->close();
$stmt_order_details->close();
$conn->close();
?>
