<?php
// Include database connection file
include_once 'db_connection.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM Customers WHERE CustomerID=$id");
    header("Location: customer.php");
    exit();
}

// Handle create and update requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $payment_info_id = $_POST['payment_info_id'];

    if (isset($_POST['CustomerID']) && !empty($_POST['CustomerID'])) {
        // Update existing customer
        $id = $_POST['CustomerID'];
        $conn->query("UPDATE Customers SET Name='$name', Email='$email', Address='$address', Phone='$phone', Username='$username', Password='$password', Payment_Info_ID='$payment_info_id' WHERE CustomerID=$id");
    } else {
        // Create new customer
        $conn->query("INSERT INTO Customers (Name, Email, Address, Phone, Username, Password, Payment_Info_ID) VALUES ('$name', '$email', '$address', '$phone', '$username', '$password', '$payment_info_id')");
    }
    header("Location: customer.php");
    exit();
}

// Retrieve customers data
$result = $conn->query("SELECT * FROM Customers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .table-container {
            max-width: 800px; /* Adjust the max-width as needed */
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-white shadow-lg">
        <div class="p-6">
            <h1 class="text-xl font-semibold mt-4 text-gray-800"> CITY PHARMACY </h1>
            <h2 class="text-xl font-semibold mt-4 text-gray-800">Admin Dashboard</h2>
            <nav class="mt-6">
                <a href="#" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-blue-500 text-white text-sm font-medium no-underline hover:bg-blue-600 hover:text-white">Dashboard</a>
                <a href= "add_product.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Products</a>
                <a href="order.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Orders</a>
                <a href="customer.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Customers</a>
            </nav>
            <!-- Close sidebar button -->
            <button id="closeSidebar" class="absolute top-0 right-0 mt-2 mr-2 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </aside>
    <!-- Menu icon -->
    
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Customers</h1>
        <div class="table-container">
            <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">CustomerID</th>
                    <th class="py-2 px-4 border-b">Name</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Address</th>
                    <th class="py-2 px-4 border-b">Phone</th>
                    <th class="py-2 px-4 border-b">Username</th>
                    <th class="py-2 px-4 border-b">Payment Info ID</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['CustomerID']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['Name']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['Email']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['Address']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['Phone']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['Username']) ?></td>
                        
                        <td class="py-2 px-4 border-b">
                    
                            <a href="customer.php?delete=<?= htmlspecialchars($row['CustomerID']) ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
                </div>
        <h2 class="text-xl font-bold mt-6 mb-4"><?php echo isset($_GET['edit']) ? 'Edit Customer' : ''; ?></h2>
        <?php
        $edit_customer = [];
        if (isset($_GET['edit'])) {
            $id = $_GET['edit'];
            $edit_result = $conn->query("SELECT * FROM Customers WHERE CustomerID=$id");
            if ($edit_result) {
                $edit_customer = $edit_result->fetch_assoc();
            }
        }
        ?>
        
        
    </div>
</body>

</html>
