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

// Retrieve user details from the database based on session data
if (isset($_SESSION['customer_id'])) {
    $user_id = $_SESSION['customer_id'];
    $sql = "SELECT * FROM Customers WHERE CustomerID = ?";
} elseif (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM Customers WHERE Username = ?";
}

$stmt = $conn->prepare($sql);
if (isset($user_id)) {
    $stmt->bind_param("i", $user_id);
} elseif (isset($username)) {
    $stmt->bind_param("s", $username);
}
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    // Fetch user details
    $user = $result->fetch_assoc();
} else {
    // Redirect to login page if user does not exist
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $update_sql = "UPDATE Customers SET Email = ?, Address = ?, Phone = ? WHERE CustomerID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $email, $address, $phone, $user['CustomerID']);

    if ($update_stmt->execute()) {
        // Successfully updated
        echo "<script>alert('Details updated successfully'); window.location.href='setting.php';</script>";
    } else {
        // Error updating
        echo "<script>alert('Error updating details');</script>";
    }
    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Settings</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Additional custom styles can be added here */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px; /* Reduced width */
            height: 100%;
            background-color: #333;
            border-top-right-radius: 30px;
            border-bottom-right-radius: 30px;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
            overflow: hidden;
        }

        .sidebar-content {
            padding: 20px;
        }

        .sidebar-link {
            display: block;
            padding: 10px 20px;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .sidebar-link:hover {
            background-color: #555;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-content">
            <h2 class="text-2xl font-semibold mb-4 text-white">CITY PHARMACY</h2>
            <!-- Sidebar links -->
            <nav>
                <ul class="space-y-2">
                    <li><a href="c_dashboard.php" class="sidebar-link">Home</a></li>
                    <li><a href="product.php" class="sidebar-link">Products</a></li>
                    <li><a href="setting.php" class="sidebar-link">Settings</a></li>
                    <li><a href="logout.php" class="sidebar-link">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="ml-64 p-6">
        <div class="container mx-auto">
            <h1 class="text-3xl font-semibold mb-6">Settings</h1>
            <!-- Content area -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Update Account Details</h2>
                <!-- Update form -->
                <form action="setting.php" method="POST">
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-semibold mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-500 focus:outline-none" required>
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-semibold mb-2">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['Address']); ?>" class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-500 focus:outline-none" required>
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['Phone']); ?>" class="w-full px-4 py-2 rounded-lg border-gray-300 focus:border-blue-500 focus:outline-none" required>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300">Update Details</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
