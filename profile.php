<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

include 'database.php';

// Fetch the user's information from the database using the session's userID
$userID = $_SESSION['userID'];
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>alert('Error fetching user details.');</script>";
}

// Determine the user role if Student or Admin
$role = 'User'; // Default role

// Check if the user is a student
$studentStmt = $conn->prepare("SELECT studentID FROM students WHERE userID = ?");
$studentStmt->bind_param("i", $userID);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();

if ($studentResult->num_rows > 0) {
    $role = 'Student';
    $studentID = $studentResult->fetch_assoc()['studentID'];
    $_SESSION['studentID'] = $studentID; // Ensure student ID is stored in the session
}

// Check if the user is an admin
$adminStmt = $conn->prepare("SELECT adminID FROM admins WHERE userID = ?");
$adminStmt->bind_param("i", $userID);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();

if ($adminResult->num_rows > 0) {
    $role = 'Admin';
    $is_admin = true;
} else {
    $is_admin = false;
}

// Fetch enrolled courses and waiting list courses if the user is a student
$enrolledCourses = [];
$waitlistedCourses = [];
if ($role === 'Student') {
    // Fetch enrolled courses
    $courseQuery = $conn->prepare("
        SELECT c.name, c.courseID 
        FROM registrations r 
        JOIN courses c ON r.courseID = c.courseID 
        WHERE r.studentID = ?
    ");
    $courseQuery->bind_param("i", $studentID);
    $courseQuery->execute();
    $coursesResult = $courseQuery->get_result();

    while ($row = $coursesResult->fetch_assoc()) {
        $enrolledCourses[] = [
            'name' => $row['name'],
            'courseID' => $row['courseID']
        ];
    }
    $courseQuery->close();

    // Fetch waiting list courses with position
    $waitlistQuery = $conn->prepare("
        SELECT c.name, c.courseID, w.position 
        FROM waitlist w 
        JOIN courses c ON w.courseID = c.courseID 
        WHERE w.studentID = ?
        ORDER BY w.position ASC
    ");
    $waitlistQuery->bind_param("i", $studentID);
    $waitlistQuery->execute();
    $waitlistResult = $waitlistQuery->get_result();

    while ($row = $waitlistResult->fetch_assoc()) {
        $waitlistedCourses[] = [
            'name' => $row['name'],
            'courseID' => $row['courseID'],
            'position' => $row['position']
        ];
    }
    $waitlistQuery->close();
}

// Function to update course enrollment numbers
function updateEnrollmentCount($conn, $courseID, $increment = true) {
    $adjustment = $increment ? 1 : -1;
    $updateCourseSql = "UPDATE courses SET enrolled = enrolled + ? WHERE courseID = ?";
    $updateStmt = $conn->prepare($updateCourseSql);
    $updateStmt->bind_param("ii", $adjustment, $courseID);
    $updateStmt->execute();
    $updateStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'Student') {
    if (isset($_POST['action']) && isset($_POST['courseID'])) {
        $courseID = $_POST['courseID'];
        $action = $_POST['action'];

        if ($action === 'enroll') {
            // Check if the course is full
            $checkCourse = $conn->prepare("SELECT capacity, enrolled FROM courses WHERE courseID = ?");
            $checkCourse->bind_param("i", $courseID);
            $checkCourse->execute();
            $courseData = $checkCourse->get_result()->fetch_assoc();
            $checkCourse->close();

            if ($courseData && $courseData['enrolled'] < $courseData['capacity']) {
                // Enroll the student
                $enrollSql = "INSERT INTO registrations (studentID, courseID) VALUES (?, ?)";
                $stmt = $conn->prepare($enrollSql);
                $stmt->bind_param("ii", $studentID, $courseID);
                $stmt->execute();
                $stmt->close();

                // Update course enrollment count
                updateEnrollmentCount($conn, $courseID, true);

                echo "<script>alert('You have successfully enrolled in the course.');</script>";
            } else {
                // If course is full, add to waiting list
                $getWaitlistPosition = $conn->prepare("
                    SELECT IFNULL(MAX(position), 0) + 1 AS nextPosition FROM waitlist WHERE courseID = ?
                ");
                $getWaitlistPosition->bind_param("i", $courseID);
                $getWaitlistPosition->execute();
                $waitlistPosition = $getWaitlistPosition->get_result()->fetch_assoc()['nextPosition'];
                $getWaitlistPosition->close();

                // Add the student to the waiting list
                $waitlistSql = "INSERT INTO waitlist (studentID, courseID, position) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($waitlistSql);
                $stmt->bind_param("iii", $studentID, $courseID, $waitlistPosition);
                $stmt->execute();
                $stmt->close();

                echo "<script>alert('The course is full. You have been added to the waitlist.');</script>";
            }
        } elseif ($action === 'remove') {
            // Remove the student from the course
            $removeSql = "DELETE FROM registrations WHERE studentID = ? AND courseID = ?";
            $stmt = $conn->prepare($removeSql);
            $stmt->bind_param("ii", $studentID, $courseID);
            $stmt->execute();
            $stmt->close();

            // Update course enrollment count
            updateEnrollmentCount($conn, $courseID, false);

            echo "<script>alert('You have successfully dropped the course.');</script>";
        }

        // Refresh the page after action
        echo "<script>window.location.href = 'profile.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = 'logout.php'; // Redirect to the logout script
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <h1>University Global Campus</h1>
    </div>
    <div class="form-container">
        <h2>User Profile</h2>
        <div class="text-center mt-4">
            <p>Here you can view your profile, enroll, and manage your courses.</p>
        </div>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></p>

        <div class="enrolled-courses">
            <?php if ($role === 'Student'): ?>
                <!-- Enrolled Courses Section -->
                <center><h3>Your Enrolled Courses</h3></center>
                <?php if (count($enrolledCourses) > 0): ?>
                    <ul>
                        <?php foreach ($enrolledCourses as $course): ?>
                            <li><?php echo htmlspecialchars($course['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <center><p>You are not enrolled in any courses.</p></center>
                <?php endif; ?>

                <!-- Waitlisted Courses Section with Position -->
                <center><h3>Your Waiting List Courses</h3></center>
                <?php if (count($waitlistedCourses) > 0): ?>
                    <ul>
                        <?php foreach ($waitlistedCourses as $course): ?>
                            <li><?php echo htmlspecialchars($course['name']); ?> (Your position number is: <?php echo $course['position']; ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <center>
                      <p>You are not on the waiting list for any courses.</p></center>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($role === 'Admin' || $role === 'Student'): ?>
                <!-- Section for viewing Enrollment Management that is visible to both Admins and Students -->
                <center><h3>Courses & Enrollments</h3></center>
                
                <?php
                // Query to get course details, enrolled students, capacity, and waiting list count
                $query = "SELECT 
                              courses.name, 
                              courses.capacity, 
                              courses.enrolled, 
                              (SELECT COUNT(*) FROM waitlist WHERE waitlist.courseID = courses.courseID) AS waitlist_count 
                          FROM courses 
                          ORDER BY courses.name";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    echo "<table class='table table-bordered mt-3'>
                            <tr class='bg-secondary text-white'>
                                <th>Course Name</th>
                                <th>Enrolled Students</th>
                                <th>Capacity</th>
                                <th>Waiting List #</th>
                            </tr>";
                    
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['enrolled']}</td>
                                <td>{$row['capacity']}</td>
                                <td>{$row['waitlist_count']}</td>
                              </tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<p>No courses found for management.</p>";
                }
                ?>
            <?php endif; ?>
        </div>

        <!-- Link to Enrollment Page -->
        <div class="links mt-4">
            <a href="enrollment.php">Enroll or Drop in a Course</a>
        </div>

        <!-- Logout Button -->
        <div class="links">
            <button class="btn btn-success" onclick="confirmLogout()">Logout</button>
        </div>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <span>&copy; 2024 Denver Doran | Course Registration System | WK-4 CST499.</span>
        </div>
    </footer>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>
