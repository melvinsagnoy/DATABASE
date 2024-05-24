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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
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
            transition: transform 0.3s ease-in-out;
            transform: translateX(-250px); /* Initially hide the sidebar */
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

        .close-sidebar {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .cart-icon, .transaction-icon {
            position: fixed;
            top: 10px;
            right: 10px;
            margin-left: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #333;
            color: white;
            border-radius: 50%;
        }

        .transaction-icon {
            right: 60px;
        }

        .cart-icon:hover, .transaction-icon:hover {
            background-color: #555;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px; /* Adjusted border radius */
            width: 80%; /* Width remains the same */
            max-width: 400px; /* Maximum width */
            height: 80%; /* Smaller height */
            max-height: 400px; /* Maximum height */
            overflow-y: auto; /* Enable vertical scrolling if needed */
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .close-modal {
            cursor: pointer;
        }

        .payment-modal {
        /* Modal styles */
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .payment-modal-content {
        /* Modal content styles */
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%; /* Adjust width as needed */
        max-width: 400px; /* Max width of the modal */
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .payment-modal-content h2 {
        margin-top: 0;
    }

    .close {
        /* Close button styles */
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .payment-option {
        /* Payment option button styles */
        display: block;
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Button colors */
    .payment-option.gcash {
        background-color: #0070f3; /* Blue */
        color: white;
    }

    .payment-option.debit-card {
        background-color: #38a169; /* Green */
        color: white;
    }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content relative">
            <span class="close-sidebar text-white" onclick="closeSidebar()">&times;</span> <!-- Close icon -->
            <h2 class="text-2xl font-semibold mb-4 text-white">Dashboard</h2>
            <!-- Sidebar links -->
            <nav>
                <ul class="space-y-2">
                    <li><a href="c_dashboard.php" class="sidebar-link">Dashboard</a></li>
                    <li><a href="product.php" class="sidebar-link">Products</a></li>
                    <li><a href="setting.php" class="sidebar-link">Settings</a></li>
                    <li><a href="logout.php" class="sidebar-link">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Menu Icon -->
    <div class="open-sidebar" onclick="openSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
    </div>

    <!-- Cart Icon -->
    <a href="cart.php" class="cart-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v2a3 3 0 01-3 3H6a3 3 0 01-3-3V3zm0 0l2.58 9.155A2 2 0 007.57 14H19m-5 4H8l3-9"></path>
        </svg>
    </a>

    <!-- Transaction Icon -->
    <div class="transaction-icon" onclick="openModal()">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
    </div>

    <!-- Modal for transaction details -->
    <div class="modal" id="transactionModal">
        <div class="modal-content relative">
            <span class="close-modal" onclick="closeModal()">&times;</span> <!-- Close icon -->
            <h2 class="text-2xl font-semibold mb-4">Order Details</h2>
            <div id="orderDetails">
                <!-- Fetch and display order details here -->
<!-- Modal for Payment Options -->
<div id="paymentModal" class="payment-modal">
    <div class="payment-modal-content">
        <span class="close" onclick="closePaymentModal()">&times;</span>
        <h2>Select Payment Method</h2>
        <button class="payment-option " style="background-color: #32cd32; color: white;" onclick=" payWithGCash()">Pay with GCash</button>
        <button class="payment-option" style="background-color: #0070f3; color: white;" onclick=" payWithDebitCard()">Pay with Debit Card</button>
    </div>
</div>

<!-- JavaScript to handle modal -->
<script>
    // Get the modal
    var paymentModal = document.getElementById('paymentModal');

    // Function to open modal
    function openPaymentModal() {
        paymentModal.style.display = 'block';
    }

    // Function to close modal
    function closePaymentModal() {
        paymentModal.style.display = 'none';
    }

    // Function to handle payment with GCash
    function payWithGCash() {
        alert('Paying with GCash');
        // Add your GCash payment logic here
    }

    // Function to handle payment with Debit Card
    function payWithDebitCard() {
        alert('Paying with Debit Card');
        // Add your Debit Card payment logic here
    }
</script>

<?php
$order_sql = "SELECT * FROM Orders WHERE CustomerID = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows > 0) {
    while ($order = $order_result->fetch_assoc()) {
        echo "<div class='mb-4'>";
        echo "<p><strong>Order ID:</strong> " . htmlspecialchars($order['OrderID']) . "</p>";
        echo "<p><strong>Order Date:</strong> " . htmlspecialchars($order['OrderDate']) . "</p>";
        echo "<p><strong>Total Amount:</strong> ₱" . number_format($order['TotalAmount'], 2) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($order['Status']) . "</p>";

        // Add pay button for each order
        echo "<div class='flex justify-between mt-2'>";
        echo "<button class='bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md' onclick='openPaymentModal()'>Pay</button>";
        echo "</div>";

        echo "</div>";
    }
} else {
    echo "<p>No orders found.</p>";
}
?>


            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="ml-64 p-6">
        <div class="container mx-auto">
            <h1 class="text-3xl font-semibold mb-6">Welcome to CITY PHARMACY, <?php echo htmlspecialchars($user['Username']); ?>!</h1>
            <!-- Content area -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4">Transaction Details</h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['Address']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['Phone']); ?></p>
            </div>
        </div>
    </main>

    <script>
        function openSidebar() {
            document.getElementById("sidebar").style.transform = "translateX(0)";
        }

        function closeSidebar() {
            document.getElementById("sidebar").style.transform = "translateX(-250px)";
        }

        function openModal() {
            document.getElementById("transactionModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("transactionModal").style.display = "none";
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

