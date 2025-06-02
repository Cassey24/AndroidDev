<?php 
session_start();
include 'connect.php';

if (!isset($_GET['user_id']) || !filter_var($_GET['user_id'], FILTER_VALIDATE_INT)) {
    die("Invalid User ID.");
}

$user_id = intval($_GET['user_id']);

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("User Not Found.");
}

$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid Email Format.");
    }

    $updateStmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ? WHERE user_id = ?");
    $updateStmt->bind_param("sssi", $firstName, $lastName, $email, $user_id);

    if ($updateStmt->execute()) {
        $_SESSION['message'] = "User Updated Successfully!";
        header("Location: display_users.php");
        exit();
    } else {
        echo "Error Updating User: " . $conn->error;
    }

    $updateStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update User</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #FFDAB3;
            text-align: center;
            padding: 20px;
            margin: 0;
        }

        /* Form container */
        .form-container {
            width: 50%;
            margin: auto;
            background: #FFF;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #C8AAAA;
            box-sizing: border-box; /* Ensures padding is inside the element */
        }

        /* Form heading */
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Labels and Inputs */
        label {
            display: block;
            margin-top: 15px;
            font-size: 16px;
            text-align: left;
        }

        input {
            width: calc(100% - 22px); /* Adjusting width to account for padding */
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #C8AAAA;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box; /* Ensures padding is included in width calculation */
        }

        /* Button Styles - Small Buttons */
        button {
            background-color: #574964;
            color: white;
            padding: 8px 16px; /* Smaller padding */
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px; /* Smaller font size */
            cursor: pointer;
        }

        button:hover {
            background-color: #9F8383;
        }

        /* Cancel Button */
        .cancel-btn {
            background-color: #f44336;
            margin-top: 10px;
            padding: 8px 16px; /* Smaller padding */
            border-radius: 5px;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .cancel-btn:hover {
            background-color: #e53935;
        }

        /* Error Message */
        .error {
            color: red;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Update User Information</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="error"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="firstName">First Name:</label>
            <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>

            <label for="lastName">Last Name:</label>
            <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit" name="update">Update</button>
            <a href="display_users.php" class="cancel-btn">Cancel</a>
        </form>
    </div>

</body>
</html>