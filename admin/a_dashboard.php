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
$result = $stmt->get_result();

// Check if admin exists
if ($result->num_rows > 0) {
    // Fetch admin details
    $admin = $result->fetch_assoc();
} else {
    // Redirect to login page if admin does not exist
    header("Location: admin_login.php");
    exit();
}

// Retrieve order data based on date filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$sql = "SELECT DATE(OrderDate) AS OrderDate, COUNT(*) AS TotalOrders 
        FROM Orders 
        WHERE OrderDate >= ? AND OrderDate <= ?
        GROUP BY DATE(OrderDate)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$dates = [];
$orderCounts = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['OrderDate'];
    $orderCounts[] = $row['TotalOrders'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
   
    </head>
   
    <body class="bg-gray-100">
    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-screen w-64 bg-white shadow-lg">
        <div class="p-6">
            <h1 class="text-xl font-semibold mt-4 text-gray-800"> CITY PHARMACY </h1>
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
        <div class="container mx-auto">
            <h1 class="text-3xl font-semibold mb-6">Welcome, <?php echo $admin['Username']; ?>!</h1>
            <!-- Content area -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Orders Graph</h2>
                <form action="" method="GET" class="mb-4">
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" class="mt-1 mb-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date:</label>
                    <input type="date" id="end_date" name="end_date" class="mt-1 mb-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">Apply Filter</button>
                </form>
                <canvas id="ordersChart" width="400" height="200"></canvas>
                <script>
                    // JavaScript code to create Chart.js bar graph
                    var ctx = document.getElementById('ordersChart').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'bar', // Change the chart type to 'bar'
                        data: {
                            labels: <?php echo json_encode($dates); ?>,
                            datasets: [{
                                label: 'Orders',
                                data: <?php echo json_encode($orderCounts); ?>,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)', // Adjust the color and transparency
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
</script>
            </div>
        </div>
    </main>
</body>
</html>
