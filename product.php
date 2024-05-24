<?php
// Include database connection file
include_once 'db_connection.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['customer_id']) || !isset($_SESSION['username'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}

// Fetch all products from the database
$sql = "SELECT * FROM Products";
$result = $conn->query($sql);

// Fetch all categories from the database
$sql_categories = "SELECT * FROM Categories";
$result_categories = $conn->query($sql_categories);

// Handle add to cart
$add_to_cart_success = false;
if (isset($_POST['add_to_cart'])) {
    // Get the customer ID from session
    $customer_id = $_SESSION['customer_id'];
    // Get the product ID from form submission
    $product_id = $_POST['product_id'];
    // Set the quantity (this could also be a form input)
    $quantity = 1;

    // Prepare and execute the insertion query
    $sql_insert = "INSERT INTO ShoppingCart (CustomerID, ProductID, Quantity) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $customer_id, $product_id, $quantity);

    if ($stmt_insert->execute()) {
        // Show a success message
        $add_to_cart_success = true;
    } else {
        // Handle error
        echo "Error: " . $stmt_insert->error;
    }

    // Close the statement
    $stmt_insert->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        /* Additional custom styles can be added here */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #333;
            border-top-right-radius: 30px;
            border-bottom-right-radius: 30px;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease-in-out;
            transform: translateX(-250px);
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

        .cart-icon {
            position: fixed;
            top: 10px;
            right: 10px;
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

        .cart-icon:hover {
            background-color: #555;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            font-size: 12px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-content relative">
            <span class="close-sidebar text-white" onclick="closeSidebar()">&times;</span>
            <h2 class="text-2xl font-semibold mb-4 text-white">Dashboard</h2>
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
    <?php
    // Query to get the total number of items in the cart for the current customer
    $customer_id = $_SESSION['customer_id'];
    $sql_cart_count = "SELECT SUM(Quantity) AS total_items FROM ShoppingCart WHERE CustomerID = $customer_id";
    $result_cart_count = $conn->query($sql_cart_count);
    $cart_count = $result_cart_count->fetch_assoc()['total_items'];
    ?>
    <?php if ($cart_count > 0): ?>
        <div class="cart-count"><?php echo $cart_count; ?></div>
    <?php endif; ?>
</a>

    <!-- Main Content -->
    <main class="ml-48 p-6">
        <div class="container mx-auto">
            <h1 class="text-3xl font-semibold mb-6">Products</h1>

            <!-- Search Form -->
            <form action="" method="GET" class="mb-4">
                <input type="text" name="search" placeholder="Search by product name or category" class="p-2 border border-gray-300 rounded-lg mr-2">
                <select name="category" class="p-2 border border-gray-300 rounded-lg mr-2">
                    <option value="">All Categories</option>
                    <?php while ($category = $result_categories->fetch_assoc()): ?>
                        <option value="<?php echo $category['CategoryID']; ?>"><?php echo htmlspecialchars($category['Name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Search</button>
            </form>

            <?php if ($add_to_cart_success): ?>
                <script>alert("Product added to cart successfully!");</script>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php
                // Check if search query exists
                if (isset($_GET['search']) || isset($_GET['category'])) {
                    $search = $_GET['search'];
                    $category = $_GET['category'];
                    // Modify the SQL query to include search and category filter functionality
                    $sql = "SELECT * FROM Products WHERE (Name LIKE '%$search%' OR Description LIKE '%$search%')";
                    if (!empty($category)) {
                        $sql .= " AND CategoryID = $category";
                    }
                    $result = $conn->query($sql);
                }
                ?>

                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="p-3">
                            <h3 class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($product['Name']); ?></h3>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($product['Description']); ?></p>
                            <p class="text-gray-800 font-semibold mt-1">₱<?php echo number_format($product['Price'], 2); ?></p>
                        </div>
                        <div class="p-3 bg-gray-100 border-t border-gray-200">
                            <form method="post" action="">
                                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                <button type="submit" name="add_to_cart" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg text-sm">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
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
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

