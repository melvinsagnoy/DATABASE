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

// Query to get the total number of items in the cart for the current customer
$customer_id = $_SESSION['customer_id'];
$sql_cart_items = "
    SELECT Products.ProductID, Products.Name, Products.Description, Products.Price, ShoppingCart.Quantity 
    FROM ShoppingCart 
    JOIN Products ON ShoppingCart.ProductID = Products.ProductID 
    WHERE ShoppingCart.CustomerID = $customer_id";
$result_cart_items = $conn->query($sql_cart_items);
$cart_count = $result_cart_items->num_rows;

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php if ($cart_count == 0): ?>
    <!-- Display message if cart is empty -->
    <div class="flex flex-col items-center justify-center h-screen bg-gray-100">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Your Cart is Empty</h1>
            <p class="text-lg text-gray-600 mb-6">It looks like you haven't added any items to your cart yet.</p>
            <a href="product.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg text-lg">Browse Products</a>
        </div>
    </div>
<?php else: ?>
    <!-- Display shopping cart items -->
    <main class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6">Shopping Cart</h1>
        <div class="bg-white shadow-md rounded-lg p-6">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Product</th>
                        <th class="py-2 px-4 border-b">Description</th>
                        <th class="py-2 px-4 border-b">Price</th>
                        <th class="py-2 px-4 border-b">Quantity</th>
                        <th class="py-2 px-4 border-b">Total</th>
                        <th class="py-2 px-4 border-b">Actions</th> <!-- New column for buttons -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $result_cart_items->fetch_assoc()): ?>
                        <tr id="item_<?php echo $item['ProductID']; ?>">
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['Name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['Description']); ?></td>
                            <td class="py-2 px-4 border-b">₱<?php echo number_format($item['Price'], 2); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($item['Quantity']); ?></td>
                            <td class="py-2 px-4 border-b">₱<?php echo number_format($item['Price'] * $item['Quantity'], 2); ?></td>
                            <td class="py-2 px-4 border-b">
                                <button class="bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-2 rounded-lg mr-2" onclick="openEditModal(<?php echo $item['ProductID']; ?>)">Edit</button>
                                <button class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2 rounded-lg" onclick="cancelOrder(<?php echo $item['ProductID']; ?>)">Cancel</button>
                            </td>
                            <div id="editModal_<?php echo $item['ProductID']; ?>" class="hidden fixed inset-0 z-50 overflow-auto bg-gray-800 bg-opacity-75 flex items-center justify-center">
    
            </form>
        </div>
    </div>
</div>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        
        <!-- Checkout button -->
        <div class="text-center mt-6">
            <a href="checkout.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg">Checkout</a>
        </div>
    </main>
<?php endif; ?>

<script>
    // Function to cancel order
    function cancelOrder(productID) {
        // Send AJAX request to delete the item
        var confirmation = confirm("Are you sure you want to cancel this item?");
        if (confirmation) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    if (xhr.status == 200) {
                        // Remove the item from the table
                        var itemRow = document.getElementById('item_' + productID);
                        if (itemRow) {
                            itemRow.parentNode.removeChild(itemRow);
                        }
                    } else {
                        alert('Error: ' + xhr.statusText);
                    }
                }
            };
            xhr.open("POST", "cancel_item.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("product_id=" + productID);
        }
    }

    // Function to edit order
    function updateOrder(productID) {
    var newQuantity = document.getElementById('editQuantity_' + productID).value;

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if (xhr.status == 200) {
                // Handle success, if needed
                closeEditModal(productID); // Close the modal
                location.reload(); // Refresh the page to reflect changes
            } else {
                alert('Error: ' + xhr.statusText);
            }
        }
    };
    xhr.open("POST", "update_item.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("product_id=" + productID + "&quantity=" + newQuantity);
}
// Function to close edit modal
function closeEditModal(productID) {
    var modal = document.getElementById('editModal_' + productID);
    modal.classList.add('hidden');
}
</script>
</body>
</html>
