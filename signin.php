<?php
include 'connect.php';
session_start();


if (isset($_POST['signIn'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email or password is empty
    if (empty($email) || empty($password)) {
        echo "Both email and password are required!";
        exit();
    }

    // Use prepared statement to avoid SQL injection
    $sql = "SELECT * FROM `users` WHERE `email` = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the email parameter to the query
    $stmt->bind_param("s", $email);

    // Execute the query
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Debugging: print the entered password, hashed password in DB, and verification result
        var_dump($password); // Entered plain password
        var_dump($row['password']); // Stored hashed password in DB
        var_dump(password_verify($password, $row['password'])); // Should return true

        // Verify the password using password_verify()
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on user role
            if ($row['role'] === 'admin') {
                header("Location: admin.php");
                exit();
            } elseif ($row['role'] === 'user') {
                header("Location: user.php");
                exit();
            } else {
                echo "Invalid role detected.";
            }
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "Email not found!";
    }

    // Close the prepared statement
    $stmt->close();
}
?>