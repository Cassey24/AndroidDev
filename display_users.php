<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['user_id']; // FIXED: was $_POST['id'] before
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);     

    if ($stmt->execute()) {
        $_SESSION['message'] = "User Deleted Successfully!";
    } else {
        $_SESSION['message'] = "Error Deleting User: " . $conn->error;
    }
    $stmt->close();
    header("Location: display_users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Table</title>
    <style> 
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #C8AAAA, #9F8383);
            text-align: center;
            padding: 30px;
            color: #574964;
        }

        h1 {
            margin-bottom: 20px;
        }

        .message {
            color: green;
            font-size: 18px;
            margin-bottom: 20px;
        }

        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            background: #FFDAB3;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 16px;
            border: 1px solid #C8AAAA;
            text-align: left;
            font-size: 16px;
        }

        th {
            background-color: #574964;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #FDEEDC;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            margin: 4px 2px;
            font-size: 15px;
            color: white;
            background-color: #574964;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #9F8383;
        }

        .btn-delete {
            background-color: #9F8383;
        }

        .btn-delete:hover {
            background-color: #574964;
        }

        form {
            display: inline;
        }

        .top-bar {
            display: flex;
            justify-content: center;
            padding: 20px 40px 0 0;
        }

        .logout-btn {
            margin-top: 50px;
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
            margin-top: 50px;
            margin-left: 10px;
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
    <h1>Users List</h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php
        $sql = "SELECT user_id, firstName, lastName, email FROM users";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['user_id']}</td>";
                echo "<td>{$row['firstName']}</td>";
                echo "<td>{$row['lastName']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>
                        <a href='update_user.php?user_id={$row['user_id']}' class='btn'>Update</a>
                        <form method='post' onsubmit='return confirm(\"Are you sure you want to delete?\")'>
                            <input type='hidden' name='user_id' value='{$row['user_id']}'>
                            <button type='submit' name='delete' class='btn btn-delete'>Delete</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No users found.</td></tr>";
        }
        $conn->close();
        ?>
    </table>

    <div class="top-bar">
        <a class="logout-btn" href="index.php">Logout</a>
        <a class="back-btn" href="admin.php">Back</a>
    </div>
</body>
</html>
