<?php
// Start the session
session_start();

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form input values
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];  // Added role input

    // Check if all fields are filled
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Check if email is already in use
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email is already registered.');</script>";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the users table
            $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);

            if ($stmt->execute()) {
                // Get the newly created userID
                $userID = $conn->insert_id;

                // Handle role assignment
                if ($role == 'student') {
                    $stmt = $conn->prepare("INSERT INTO students (userID) VALUES (?)");
                    $stmt->bind_param("i", $userID);
                } elseif ($role == 'admin') {
                    $stmt = $conn->prepare("INSERT INTO admins (userID) VALUES (?)");
                    $stmt->bind_param("i", $userID);
                }

                // Execute role insert
                if ($stmt->execute()) {
                    echo "<script>alert('Registration successful! Redirecting to login...'); window.location.href = 'login.php';</script>";
                } else {
                    echo "<script>alert('Failed to assign role. Please try again later.');</script>";
                }
            } else {
                echo "<script>alert('Registration failed. Please try again later.');</script>";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="header">
        <h1>University Global Campus</h1>
    </div>
    <form method="POST" action="register.php">
        <div class="form-container">
            <h2>Register</h2>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" class="form-control" name="phone" id="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success">Register</button>
            </div>
            <div class="links">
                <a href="login.php">Already have an account? Login</a>
            </div>
            <div class="links">
                <a href="index.php">Back to Hompage</a>
            </div>
        </div>
    </form>
    <footer class="footer">
        <div class="container text-center">
            <span>&copy; 2024 Denver Doran | Course Registration System | WK-4 CST499.</span>
        </div>
    </footer>
</body>
</html>
