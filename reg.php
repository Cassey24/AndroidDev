<?php 
include 'connect.php';

if (isset($_POST['signUp'])) {
    $firstName = trim($_POST['FName']);
    $lastName = trim($_POST['lName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $role = $_POST['role'];

    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($cpassword) || empty($role)) {
        echo "All fields are required!";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid Email Format!";
        exit();
    }

    if ($password !== $cpassword) {
        echo "Passwords do not match!";
        exit();
    }

    if (strlen($password) < 8) {
        echo "Password must be at least 8 characters long!";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check for existing email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email Address Already Exists!";
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Insert new user without specifying the ID
    $insertStmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

    if ($insertStmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $insertStmt->close();
}
?>