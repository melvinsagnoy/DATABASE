<?php
// Check if user is logged in
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Include database connection file
include_once 'db_connection.php';

// Process prescription upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $customer_id = $_SESSION['customer_id']; // Assuming you store customer ID in session
    $prescription_date = $_POST['prescription_date'];
    $doctor_name = $_POST['doctor_name'];
    $prescription_details = $_POST['prescription_details'];

    // Insert prescription data into database
    $sql = "INSERT INTO Prescriptions (CustomerID, PrescriptionDate, DoctorName, PrescriptionDetails) 
            VALUES ('$customer_id', '$prescription_date', '$doctor_name', '$prescription_details')";
    if ($conn->query($sql) === TRUE) {
        // Prescription upload successful
        echo "Prescription uploaded successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!-- HTML prescription upload form -->
<!DOCTYPE html>
<html>
<head>
    <title>Upload Prescription</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Upload Prescription</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Form fields -->
            <input type="date" name="prescription_date" required><br>
            <input type="text" name="doctor_name" placeholder="Doctor Name" required><br>
            <textarea name="prescription_details" placeholder="Prescription Details" rows="4" cols="50" required></textarea><br>
            <input type="submit" value="Upload Prescription">
        </form>
    </div>
</body>
</html>