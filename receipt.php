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

    // Retrieve cart items for the logged-in user from the database
    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $sql = "SELECT ShoppingCart.CartID, Products.Name AS ProductName, Products.Description, Products.Price, ShoppingCart.Quantity
                FROM ShoppingCart
                JOIN Products ON ShoppingCart.ProductID = Products.ProductID
                WHERE ShoppingCart.CustomerID = ?";
    } elseif (isset($_SESSION['username'])) {
        // If using username instead of customer_id
        $username = $_SESSION['username'];
        $sql = "SELECT ShoppingCart.CartID, Products.Name AS ProductName, Products.Description, Products.Price, ShoppingCart.Quantity
                FROM ShoppingCart
                JOIN Products ON ShoppingCart.ProductID = Products.ProductID
                JOIN Customers ON ShoppingCart.CustomerID = Customers.CustomerID
                WHERE Customers.Username = ?";
    }

    $stmt = $conn->prepare($sql);
    if (isset($customer_id)) {
        $stmt->bind_param("i", $customer_id);
    } elseif (isset($username)) {
        $stmt->bind_param("s", $username);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Close the prepared statement
    $stmt->close();

    // Calculate total price
    $total_price = 0;

    // HTML for the receipt
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Receipt</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #fafafa;
                padding: 20px;
            }
            .container {
                max-width: 400px;
                margin: 0 auto;
                background-color: #fff;
                border: 1px solid #ccc;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            h2 {
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 8px 0;
                border-bottom: 1px solid #ddd;
            }
            th {
                text-align: left;
                font-weight: bold;
            }
            td {
                color: #333;
            }
            tfoot {
                font-weight: bold;
            }
            .total {
                text-align: right;
            }
            .pdf-btn {
                text-align: center;
                margin-top: 20px;
            }
            .btn {
                background-color: #007bff;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            .btn:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Receipt</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_price = 0;
                    while ($row = $result->fetch_assoc()) : 
                        $total = $row['Price'] * $row['Quantity'];
                        $total_price += $total;
                    ?>
                        <tr>
                            <td><?php echo $row['ProductName']; ?></td>
                            <td><?php echo $row['Quantity']; ?></td>
                            <td>$<?php echo $row['Price']; ?></td>
                            <td>$<?php echo number_format($total, 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="total">
                        <td colspan="3">Total:</td>
                        <td>$<?php echo number_format($total_price, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            <div class="pdf-btn">
                <form action="generate_pdf.php" method="post">
                    <button type="submit" class="btn">Save as PDF</button>
                </form>
            </div>
        </div>
    </body>
    </html>


    <?php
    // Close the database connection
    $conn->close();
    ?>
