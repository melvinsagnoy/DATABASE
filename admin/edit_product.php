<?php
// Include database connection file
include_once 'db_connection.php';

// Process product update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];
    $manufacturer_id = $_POST['manufacturer_id'];
    $is_prescription_required = isset($_POST['is_prescription_required']) ? 1 : 0; // Check if prescription is required

    // Update product data in database
    $sql = "UPDATE Products SET Name='$name', Description='$description', Price='$price', Quantity='$quantity', 
            CategoryID='$category_id', ManufacturerID='$manufacturer_id', IsPrescriptionRequired='$is_prescription_required' 
            WHERE ProductID='$product_id'";
    if ($conn->query($sql) === TRUE) {
        // Product update successful
        echo "Product updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!-- HTML product update form -->
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Product</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Form fields -->
            <input type="hidden" name="product_id" value="<?php echo $_GET['id']; ?>">
            <input type="text" name="name" placeholder="Product Name" required><br>
            <textarea name="description" placeholder="Description" rows="4" cols="50" required></textarea><br>
            <input type="number" name="price" placeholder="Price" required><br>
            <input type="number" name="quantity" placeholder="Quantity" required><br>
            <input type="number" name="category_id" placeholder="Category ID" required><br>
            <input type="number" name="manufacturer_id" placeholder="Manufacturer ID" required><br>
            <input type="checkbox" name="is_prescription_required"> Prescription Required<br>
            <input type="submit" value="Update Product">
        </form>
    </div>
</body>
</html>