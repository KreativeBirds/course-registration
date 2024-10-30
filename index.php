<?php
session_start();
// Start user the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                alert("You have been logged out. Redirecting to the home page...");
                window.location.href = 'logout.php'; // Redirect to the logout script
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>University Global Campus</h1>
	</div>
	<div class="container-home">
        <div class="jumbotron text-center mt-5">
            <h3>Welcome to the Course Registration System</h3>
            <p>To access our course registration system, you will need to either log in or create an account. 
            New users can register by providing their personal details, and upon successful registration, 
            students can view available courses, enroll, and manage their profiles. Admins will be able to 
            manage courses and monitor enrollments. If you already have an account, simply log in to 
            proceed with course management.</p>
			<br></br>
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-success">Register</a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Denver Doran | Course Registration System | WK-4 CST499.</p>
    </footer>
</body>
</html>
