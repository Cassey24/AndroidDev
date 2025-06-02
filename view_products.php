<?php
session_start();
include 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products List</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #C8AAAA, #9F8383);
            margin: 0;
            padding: 0;
            color: #574964;
        }

        h1 {
            text-align: center;
            margin-top: 30px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }

        .input-box {
            background-color: #FFDAB3;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .input-box form .box {
            width: 90%;
            height: 40px;
            border-radius: 5px;
            padding: 0.5rem;
            font-size: 16px;
            margin: 0.5rem 0;
            border: 1px solid #C8AAAA;
            background-color: #fff;
            color: #574964;
        }

        .btn {
            width: 90%;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px;
            background-color: #574964;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #9F8383;
        }

        .tablep {
            width: 90%;
            margin: 40px auto;
        }

        .tablep table {
            width: 100%;
            border-collapse: collapse;
            background-color: #FFDAB3;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 16px;
            border: 1px solid #C8AAAA;
            text-align: left;
        }

        th {
            background-color: #574964;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #FDEEDC;
        }

        .update-btn,
        .delete-btn {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .update-btn {
            background-color: #574964;
        }

        .update-btn:hover {
            background-color: #9F8383;
        }

        .delete-btn {
            background-color: #9F8383;
        }

        .delete-btn:hover {
            background-color: #574964;
        }

        .top-bar {
    display: flex;
    justify-content: center;
    padding: 20px 40px 0 0;
}

        .logout-btn {
    margin-top: 20px;
    text-decoration: none;
    color: white;
    background-color: #574964;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 16px;
    transition: background-color 0.3s;
    
}

.logout-btn:hover {
    background-color: #9F8383;
}

.back-btn {
    margin-top: 20px;
    margin-left: 10px; /* Adds space between the buttons */
    text-decoration: none;
    color: white;
    background-color: #574964;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 16px;
    transition: background-color 0.3s;
}

.back-btn:hover {
    background-color: #9F8383;
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Enter Product</h1>
        <div class="input-box">
            <form method="post" action="add_products.php">
                <input type="text" placeholder="Enter Product Name" name="ProductName" class="box" required>
                <input type="text" placeholder="Enter Brand Name" name="Brand" class="box" required> 
                <input type="number" step="0.01" placeholder="Enter Price" name="Price" class="box" required> 
                <input type="number" placeholder="Enter Quantity" name="Quantity" class="box" required> 
                <input type="text" placeholder="Enter Description" name="Description" class="box" required> 
                <input type="submit" class="btn" name="add_product" value="ADD PRODUCT">
            </form>
        </div>
    </div>

    <div class="tablep">
        <?php if (isset($_GET['deleted'])): ?>
        <script>alert('Product deleted successfully.');</script>
        <?php endif; ?>
        
        <h1>Products List</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT product_id, ProductName, Brand, Price, Quantity, Description FROM products";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['product_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ProductName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Brand']) . "</td>";
                        echo "<td>" . htmlspecialchars(number_format($row['Price'], 2)) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                        echo "<td>
                                <a href='update_product.php?id=" . $row['product_id'] . "' class='update-btn'>Update</a> 
                                <a href='delete_product.php?id=" . $row['product_id'] . "' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this product?');\">Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No products found.</td></tr>";
                }
                $result->free();
            } else {
                echo "<tr><td colspan='7'>Error: " . $conn->error . "</td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>

    <div class="top-bar">
    <a class="logout-btn" href="index.php">Logout</a>
    <a class="back-btn" href="admin.php">Back</a>
</div>

</body>
</html>
