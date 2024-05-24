<?php
// Include database connection file
include_once 'db_connection.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $productName = $_POST['productName'];
    $productDescription = $_POST['productDescription'];
    $productPrice = $_POST['productPrice'];
    $productQuantity = $_POST['productQuantity'];
    $categoryID = $_POST['category'];
    $manufacturerID = $_POST['manufacturer'];
    $isPrescriptionRequired = isset($_POST['isPrescriptionRequired']) ? 1 : 0;

    // Upload product image
    $targetDir = "../uploads/";
    $targetFile = $targetDir . basename($_FILES["productImage"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["productImage"]["tmp_name"]);
    if ($check !== false) {
        // File is an image
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["productImage"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $targetFile)) {
            // The file has been uploaded successfully.
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    // Insert product into database
    $sql_insert_product = "INSERT INTO Products (Name, Description, Price, Quantity, CategoryID, ManufacturerID, Image, IsPrescriptionRequired) 
                           VALUES ('$productName', '$productDescription', '$productPrice', '$productQuantity', '$categoryID', '$manufacturerID', '$targetFile', '$isPrescriptionRequired')";

    if ($conn->query($sql_insert_product) === TRUE) {
        // New product added successfully, redirect to add_product.php
        header("Location: add_product.php");
        exit();
    } else {
        echo "Error: " . $sql_insert_product . "<br>" . $conn->error;
    }
}
?>
