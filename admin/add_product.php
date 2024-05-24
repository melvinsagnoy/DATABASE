<?php
// Include database connection file
include_once 'db_connection.php';

// Start session
session_start();

// Check if admin is logged in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $productName = isset($_POST['productName']) ? $_POST['productName'] : '';
    $productDescription = isset($_POST['productDescription']) ? $_POST['productDescription'] : '';
    $productPrice = isset($_POST['productPrice']) ? $_POST['productPrice'] : '';
    $productQuantity = isset($_POST['productQuantity']) ? $_POST['productQuantity'] : '';
    $categoryID = isset($_POST['category']) ? $_POST['category'] : '';
    $manufacturerID = isset($_POST['manufacturer']) ? $_POST['manufacturer'] : '';
    $isPrescriptionRequired = isset($_POST['isPrescriptionRequired']) ? 1 : 0;

    // Check if all required fields are set
    if (!empty($productName) && !empty($productDescription) && !empty($productPrice) && !empty($productQuantity) && !empty($categoryID) && !empty($manufacturerID)) {
        // Handle file upload
        $targetDir = "../uploads/";
        $targetFile = $targetDir . basename($_FILES["productImage"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["productImage"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["productImage"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $targetFile)) {
                echo "The file ". htmlspecialchars( basename( $_FILES["productImage"]["name"])). " has been uploaded.";
                
                // Escape special characters to prevent SQL injection
                $productName = mysqli_real_escape_string($conn, $productName);
                $productDescription = mysqli_real_escape_string($conn, $productDescription);
                $productPrice = mysqli_real_escape_string($conn, $productPrice);
                $productQuantity = mysqli_real_escape_string($conn, $productQuantity);
                $categoryID = mysqli_real_escape_string($conn, $categoryID);
                $manufacturerID = mysqli_real_escape_string($conn, $manufacturerID);
                $productImage = mysqli_real_escape_string($conn, $targetFile);

                // Insert into Products table
                $sql_insert_product = "INSERT INTO Products (Name, Description, Price, Quantity, CategoryID, ManufacturerID, IsPrescriptionRequired, Image) 
                                        VALUES ('$productName', '$productDescription', '$productPrice', '$productQuantity', '$categoryID', '$manufacturerID', '$isPrescriptionRequired', '$productImage')";
                
                if ($conn->query($sql_insert_product) === TRUE) {
                    echo "Product added successfully";
                } else {
                    echo "Error adding product: " . $conn->error;
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        echo "All fields are required.";
    }
}

// Query to fetch products
$sql_products = "SELECT p.ProductID, p.Name, p.Description, p.Price, p.Quantity, c.Name AS CategoryName, m.Name AS ManufacturerName, p.IsPrescriptionRequired, p.Image 
                FROM Products p 
                LEFT JOIN Categories c ON p.CategoryID = c.CategoryID 
                LEFT JOIN Manufacturers m ON p.ManufacturerID = m.ManufacturerID";
$result_products = $conn->query($sql_products);

// Query to fetch categories
$sql_categories = "SELECT CategoryID, Name AS CategoryName FROM Categories";
$result_categories = $conn->query($sql_categories);

// Query to fetch manufacturers
$sql_manufacturers = "SELECT ManufacturerID, Name AS ManufacturerName FROM Manufacturers";
$result_manufacturers = $conn->query($sql_manufacturers);

// Check if there was an error with the product query
if ($result_products === false) {
    die("Error executing product query: ". $conn->error);
}

// Check if there was an error with the category query
if ($result_categories === false) {
    die("Error executing category query: ". $conn->error);
}

// Check if there was an error with the manufacturer query
if ($result_manufacturers === false) {
    die("Error executing manufacturer query: ". $conn->error);
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
    <style>.product-card {
            height: 400px; /* Set a fixed height for the card */
        }

        .product-image {
            height: 150px; /* Set a smaller fixed height for the image */
            width: auto; /* Maintain aspect ratio */
            object-fit: contain; /* Maintain aspect ratio and fit within the specified height */
        }</style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-6">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button id="addProductBtn" class="bg-blue-500 text-white px-4 py-2 rounded">Add Product</button>
</div>

    
    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-screen w-64 bg-white shadow-lg">
        <div class="p-6">
        <h1 class="text-xl font-semibold mt-4 text-gray-800">CITY PHARMACY  </h1>
            <h2 class="text-xl font-semibold mt-4 text-gray-800">Admin Dashboard</h2>
            <nav class="mt-6">
                <a href="a_dashboard.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-blue-500 text-white text-sm font-medium no-underline hover:bg-blue-600 hover:text-white">Dashboard</a>
                <a href="add_product.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Products</a>
                <a href="order.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Orders</a>
                <a href="customer.php" class="block py-2.5 px-4 rounded transition duration-150 ease-in-out bg-gray-200 text-gray-700 text-sm font-medium no-underline hover:bg-gray-300 hover:text-gray-900">Customers</a>
            </nav>
        </div>
    </aside>

  <!-- Main Content -->
  <main class="ml-64 p-6">
        <div class="container">
            <h2 class="text-2xl font-semibold mb-4">All Products</h2>
            <div class="grid grid-cols-3 gap-4">
                <?php
                if ($result_products && $result_products->num_rows > 0) {
                    while ($row = $result_products->fetch_assoc()) {
                        echo '<div class="bg-white p-4 rounded shadow product-card">';
                        echo '<h3 class="font-bold">'. htmlspecialchars($row['Name']). '</h3>';
                        echo '<p>'. htmlspecialchars($row['Description']). '</p>';
                        echo '<p>Price: $'. htmlspecialchars($row['Price']). '</p>';
                        echo '<p>Quantity: '. htmlspecialchars($row['Quantity']). '</p>';
                        echo '<p>Category: '. htmlspecialchars($row['CategoryName']). '</p>';
                        echo '<p>Manufacturer: '. htmlspecialchars($row['ManufacturerName']). '</p>';
                        echo '<p>Prescription Required: '. ($row['IsPrescriptionRequired'] ? 'Yes' : 'No'). '</p>';
                        // Display product image
                        echo '<img src="'. htmlspecialchars($row['Image']). '" alt="'. htmlspecialchars($row['Name']). '" class="w-full h-full object-contain product-image">';
                        // Update button
                        echo '<a href="update_product.php?product_id='. $row['ProductID'] .'" class="bg-blue-500 text-white px-4 py-2 rounded">Update</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No products found.</p>';
                }
                ?>
            </div>
        </div>
    </main>

    <!-- Modal for adding product -->
    <div id="addProductModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-semibold mb-4">Add New Product</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
                <input type="text" id="productName" name="productName" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="productDescription" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="productDescription" name="productDescription" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md"></textarea>
            </div>
            <div class="mb-4">
                <label for="productPrice" class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" step="0.01" id="productPrice" name="productPrice" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="productQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" id="productQuantity" name="productQuantity" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="category" name="category" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md bg-white">
                    <?php
                    if ($result_categories && $result_categories->num_rows > 0) {
                        while ($category = $result_categories->fetch_assoc()) {
                            echo '<option value="'. htmlspecialchars($category['CategoryID']) .'">'. htmlspecialchars($category['CategoryName']) .'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="manufacturer" class="block text-sm font-medium text-gray-700">Manufacturer</label>
                <select id="manufacturer" name="manufacturer" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md bg-white">
                    <?php
                    if ($result_manufacturers && $result_manufacturers->num_rows > 0) {
                        while ($manufacturer = $result_manufacturers->fetch_assoc()) {
                            echo '<option value="'. htmlspecialchars($manufacturer['ManufacturerID']) .'">'. htmlspecialchars($manufacturer['ManufacturerName']) .'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="productImage" class="block text-sm font-medium text-gray-700">Image</label>
                <input type="file" id="productImage" name="productImage" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md bg-white">
            </div>
            <div class="mb-4">
                <label for="isPrescriptionRequired" class="block text-sm font-medium text-gray-700">Is Prescription Required?</label>
                <input type="checkbox" id="isPrescriptionRequired" name="isPrescriptionRequired" class="mt-1">
            </div>
            <div class="flex justify-end">
                <button type="button" id="cancelBtn" class="bg-red-500 text-white px-4 py-2 rounded mr-2">Cancel</button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Product</button>
            </div>
        </form>
    </div>
</div>

    <!-- JavaScript to handle modal visibility -->
    <script>
        const addProductBtn = document.getElementById('addProductBtn');
        const addProductModal = document.getElementById('addProductModal');
        const cancelBtn = document.getElementById('cancelBtn');

        addProductBtn.addEventListener('click', () => {
            addProductModal.classList.remove('hidden');
        });

        cancelBtn.addEventListener('click', () => {
            addProductModal.classList.add('hidden');
        });
    </script>
</body>
</html>
