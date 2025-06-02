<?php
session_start();
include 'connect.php';

// Handle delete order
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $orderId = $_POST['order_id'];

    // Delete the order items first
    $sql_delete_items = "DELETE FROM order_items WHERE order_id = $orderId";
    if ($conn->query($sql_delete_items) === TRUE) {
        // Now delete the order
        $sql_delete_order = "DELETE FROM orders WHERE id = $orderId";
        if ($conn->query($sql_delete_order) === TRUE) {
            $_SESSION['message'] = "Order Deleted Successfully!";
        } else {
            $_SESSION['message'] = "Error Deleting Order: " . $conn->error;
        }
    } else {
        $_SESSION['message'] = "Error Deleting Order Items: " . $conn->error;
    }

    header("Location: view_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Table</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style> 
       body {
    font-family: Arial, sans-serif;
    background-color: #9F8383;
    color: #574964;
    text-align: center;
    padding: 20px;
}

.message {
    color: #574964;
    background-color: #FFDAB3;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    display: inline-block;
}

h1 {
    color: #574964;
    margin-bottom: 20px;
}

table {
    width: 90%;
    margin: 0 auto 30px auto;
    border-collapse: collapse;
    background: #C8AAAA;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

th, td {
    padding: 14px;
    border: 1px solid #574964;
    text-align: left;
}

th {
    background-color: #574964;
    color: #FFDAB3;
}

tr:hover {
    background-color: #FFDAB3;
}

.btn-delete i {
    color: #574964;
    transition: color 0.3s ease;
}

.btn-delete:hover i {
    color: red;
}

.action-row {
    display: flex;
    flex-direction: column;
    gap: 5px;
    align-items: center;
}

select.form-select {
    background-color: #FFF;
    border: 1px solid #574964;
    padding: 5px 10px;
    color: #574964;
    border-radius: 5px;
}

.expandable-content {
    display: none;
    background-color: #FFDAB3;
    padding: 15px;
    text-align: left;
    color: #574964;
}

.show-details {
    background-color: #574964;
    color: #FFDAB3;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.show-details:hover {
    background-color: #9F8383;
    color: white;
}

/* Status color classes */
.status-received {
    color: green;
    font-weight: bold;
}

.status-cancelled {
    color: red;
    font-weight: bold;
}

.status-pending {
    color: orange;
    font-weight: bold;
}

.status-preparing {
    color: blue;
    font-weight: bold;
}

.status-out-for-delivery {
    color: #f39c12;
    font-weight: bold;
}

.status-ready-for-pickup {
    color: #27ae60;
    font-weight: bold;
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
    <h1>Orders List</h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Email</th>
            <th>Full Name</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Action</th>
        </tr>
        <?php
        // Query to fetch orders and order items
        $sql = "SELECT o.id, o.email, o.first_name, o.last_name, oi.product_name, oi.quantity, oi.total, o.status, o.payment_method, o.address, o.contact_no, o.created_at
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id";

        $result = $conn->query($sql);
        if ($result === false) {
            echo "<tr><td colspan='9'>Error fetching orders: " . $conn->error . "</td></tr>";
        } else {
            if ($result->num_rows > 0) {
                $orders = [];
                while ($row = $result->fetch_assoc()) {
                    $orders[$row['id']][] = $row;
                }

                foreach ($orders as $orderId => $orderItems) {
                    $order = $orderItems[0];
                    $statusClass = '';
                
                    // Determine status color class
                    switch ($order['status']) {
                        case 'Order Received':
                            $statusClass = 'status-received';
                            break;
                        case 'Cancelled':
                            $statusClass = 'status-cancelled';
                            break;
                        case 'Pending':
                            $statusClass = 'status-pending';
                            break;
                        case 'Preparing':
                            $statusClass = 'status-preparing';
                            break;
                        case 'Out for Delivery':
                            $statusClass = 'status-out-for-delivery';
                            break;
                        case 'Ready for Pickup':
                            $statusClass = 'status-ready-for-pickup';
                            break;
                        default:
                            $statusClass = 'status-received'; // Default to 'Order Received' style if status is unknown
                    }
                
                    echo "<tr class='expandable-row' data-order-id='" . $order['id'] . "'>";
                    echo "<td>" . $order['id'] . "</td>";
                    echo "<td>" . $order['email'] . "</td>";
                    echo "<td>" . $order['first_name'] . " " . $order['last_name'] . "</td>";
                    echo "<td>" . implode("<br>", array_column($orderItems, 'product_name')) . "</td>";
                    echo "<td>" . implode("<br>", array_column($orderItems, 'quantity')) . "</td>";
                    echo "<td>" . array_sum(array_column($orderItems, 'total')) . "</td>";
                
                    // Display status with color
                    echo "<td class='" . $statusClass . "'>" . $order['status'] . "</td>";
                    echo "<td>" . $order['payment_method'] . "</td>";
                
                    // Action Column 
                    $dropdownDisabled = ($order['status'] == 'Order Received' || $order['status'] == 'Cancelled') ? 'disabled' : '';
                    echo "<td class='action-row'>
                            <form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete?\")'>
                                <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                                <button type='submit' name='delete' class='btn-delete'>
                                    <i class='fas fa-trash'></i> <!-- Trash icon -->
                                </button>
                            </form>
                            <div>
                                <select class='form-select' style='width: 120px;' data-order-id='" . $order['id'] . "' " . $dropdownDisabled . ">
                                    <option value='Pending' " . ($order['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='Preparing' " . ($order['status'] == 'Preparing' ? 'selected' : '') . ">Preparing</option>";
                                    if ($order['payment_method'] == 'COD') {
                                        echo "<option value='Out for Delivery' " . ($order['status'] == 'Out for Delivery' ? 'selected' : '') . ">Out for Delivery</option>";
                                    } elseif ($order['payment_method'] == 'PayAtStore') {
                                        echo "<option value='Ready for Pickup' " . ($order['status'] == 'Ready for Pickup' ? 'selected' : '') . ">Ready for Pickup</option>";
                                    }
                    echo "   <option value='Cancelled' " . ($order['status'] == 'Cancelled' ? 'selected' : '') . ">Cancelled</option>
                            </select>
                        </div>
                      </td>";
                    echo "</tr>";
                
                    // Add expandable content row
                    echo "<tr class='expandable-content' data-order-id='" . $order['id'] . "'>
                    <td colspan='9'>
                        <div><strong>Address:</strong> " . $orderItems[0]['address'] . "</div>
                        <div><strong>Contact No:</strong> " . $orderItems[0]['contact_no'] . "</div>
                        <div><strong>Order Date:</strong> " . date("F j, Y, g:i a", strtotime($orderItems[0]['created_at'])) . "</div>
                    </td>
                    </tr>";
                    // Add Show Details Button
                    echo "<tr><td colspan='9'>
                            <button class='show-details' data-order-id='" . $order['id'] . "'>Show Details</button>
                          </td></tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No orders found.</td></tr>";
            }
        }
        ?>
    </table>

    <script>
    $(document).ready(function() {
        // Event listener for the Show Details button
        $('.show-details').click(function() {
            var orderId = $(this).data('order-id');
            var expandableContentRow = $("tr[data-order-id='" + orderId + "'].expandable-content");
            
            // Toggle the display of the expandable content
            expandableContentRow.toggle();
            
            // Change button text based on whether the row is shown or hidden
            var button = $(this);
            if (expandableContentRow.is(':visible')) {
                button.text('Hide Details');
            } else {
                button.text('Show Details');
            }
        });

        // Event listener for the status change
        $('.form-select').change(function() {
            var orderId = $(this).data('order-id');
            var status = $(this).val();
            
            $.ajax({
                url: 'update_order_status.php',
                method: 'POST',
                data: {
                    order_id: orderId,
                    status: status
                },
                success: function(response) {
                    console.log("Response from server:", response);  // Log the full response
                    try {
                        var res = JSON.parse(response);
                        console.log(res); // Check if JSON.parse succeeds
                        if (res.status === 'success') {
                            var statusCell = $("tr[data-order-id='" + orderId + "'] td.status");
                            statusCell.text(status);
                            statusCell.removeClass().addClass('status-' + status.toLowerCase().replace(' ', '-'));
                            if (res.reload) {
                                console.log('Reloading page...');
                                location.reload();
                            }
                        } else {
                            alert('Error updating order status');
                        }
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        alert('Invalid response received');
                    }
                }
            });
        });
    });


    </script>

<div class="top-bar">
    <a class="logout-btn" href="index.php">Logout</a>
    <a class="back-btn" href="admin.php">Back</a>
</div>


</body>
</html>
