<?php
// Include the database configuration file
include 'database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate the form data
    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in both email and password.');</script>";
    } else {
        // Check if the user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Store user data in session variables
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['user_name'] = $user['name'];
                
                // Optional: If the user is a student, store their student ID
                $studentStmt = $conn->prepare("SELECT studentID FROM students WHERE userID = ?");
                $studentStmt->bind_param("i", $user['userID']);
                $studentStmt->execute();
                $studentResult = $studentStmt->get_result();
                
                if ($studentResult->num_rows > 0) {
                    $student = $studentResult->fetch_assoc();
                    $_SESSION['studentID'] = $student['studentID'];
                }
                $studentStmt->close();

                // Redirect to profile page
                header("Location: profile.php");
                exit;
            } else {
                echo "<script>alert('Incorrect password. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('No user found with that email.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="header">
        <h1>University Global Campus</h1>
    </div>
    <form method="POST" action="login.php">
        <div class="form-container">
            <h2>Login</h2>
            <form action="login.php" method="post" class="mt-4">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success">Login</button>
                </div>
            </form>
            <div class="links">
                <a href="register.php">Don't have an account? Register</a>
            </div>
            <div class="links">
                <a href="index.php">Back to Homepage</a>
            </div>
    </form>
	</div>

    <footer class="footer">
        <div class="container text-center">
            <span>&copy; 2024 Denver Doran | Course Registration System | WK-4 CST499.</span>
        </div>
    </footer>
</body>
</html>
