<?php
// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'database.php';

// Ensure only logged-in users can access this page
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

// Fetch the user's role and determine if they are an admin or student
$userID = $_SESSION['userID'];
$role = 'User'; // Default role is User

// Check if the user is a student
$studentStmt = $conn->prepare("SELECT studentID FROM students WHERE userID = ?");
$studentStmt->bind_param("i", $userID);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();

if ($studentResult->num_rows > 0) {
    $role = 'Student';
    $studentID = $studentResult->fetch_assoc()['studentID'];
    $_SESSION['studentID'] = $studentID;
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

// Fetch enrolled courses if the user is a student
$enrolledCourses = [];
if ($role === 'Student') {
    $courseQuery = $conn->prepare("
        SELECT c.name 
        FROM registrations r 
        JOIN courses c ON r.courseID = c.courseID 
        WHERE r.studentID = ?
    ");
    $courseQuery->bind_param("i", $studentID);
    $courseQuery->execute();
    $coursesResult = $courseQuery->get_result();

    while ($row = $coursesResult->fetch_assoc()) {
        $enrolledCourses[] = $row['name'];
    }

    $courseQuery->close();
}

// This handles course enrollment, removal for students, and the waiting list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $role === 'Student') {
    $courseID = $_POST['courseID'];
    $action = $_POST['action']; // "enroll, remove or remove from waiting list

    if ($action === 'enroll') {
        // This allows users to enroll in the selected course if not full
        $checkCourse = $conn->prepare("SELECT capacity, enrolled FROM courses WHERE courseID = ?");
        $checkCourse->bind_param("i", $courseID);
        $checkCourse->execute();
        $courseData = $checkCourse->get_result()->fetch_assoc();
        $checkCourse->close();

        if ($courseData && $courseData['enrolled'] < $courseData['capacity']) {
            // Enroll student
            $enrollSql = "INSERT INTO registrations (studentID, courseID) VALUES (?, ?)";
            $stmt = $conn->prepare($enrollSql);
            $stmt->bind_param("ii", $studentID, $courseID);
            $stmt->execute();
            $stmt->close();

            // Update course enrollment count
            $updateCourseSql = "UPDATE courses SET enrolled = enrolled + 1 WHERE courseID = ?";
            $updateStmt = $conn->prepare($updateCourseSql);
            $updateStmt->bind_param("i", $courseID);
            $updateStmt->execute();
            $updateStmt->close();

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
        // This removes the student from the course
        $removeSql = "DELETE FROM registrations WHERE studentID = ? AND courseID = ?";
        $stmt = $conn->prepare($removeSql);
        $stmt->bind_param("ii", $studentID, $courseID);
        $stmt->execute();
        $stmt->close();

        // This updates the course enrollment count
        $updateCourseSql = "UPDATE courses SET enrolled = enrolled - 1 WHERE courseID = ?";
        $updateStmt = $conn->prepare($updateCourseSql);
        $updateStmt->bind_param("i", $courseID);
        $updateStmt->execute();
        $updateStmt->close();

        echo "<script>alert('You have successfully dropped the course.');</script>";
    } elseif ($action === 'removeFromWaitlist') {
        // Tis removes the student from the waiting list
        $removeWaitlistSql = "DELETE FROM waitlist WHERE studentID = ? AND courseID = ?";
        $stmt = $conn->prepare($removeWaitlistSql);
        $stmt->bind_param("ii", $studentID, $courseID);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('You have been removed from the waitlist.');</script>";
    }

    // This refresh the page after any action
    echo "<script>window.location.href = 'enrollment.php';</script>";
    exit();
}

// This allows Admin to remove a student from a course or waiting list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeStudent']) && $is_admin) {
    $registrationID = $_POST['registrationID'];
    $waitlistID = $_POST['waitlistID'] ?? null;

    if ($registrationID) {
        // This removes the student from the course
        $removeSql = "DELETE FROM registrations WHERE registrationID = ?";
        $removeStmt = $conn->prepare($removeSql);
        $removeStmt->bind_param("i", $registrationID);
        $removeStmt->execute();
        $removeStmt->close();

        // This updates the course enrollment count
        $updateCourseSql = "UPDATE courses SET enrolled = enrolled - 1 WHERE courseID = (SELECT courseID FROM registrations WHERE registrationID = ?)";
        $updateCourseStmt = $conn->prepare($updateCourseSql);
        $updateCourseStmt->bind_param("i", $registrationID);
        $updateCourseStmt->execute();
        $updateCourseStmt->close();

        echo "<script>alert('Student removed from the course successfully.');</script>";
    } elseif ($waitlistID) {
        // Remove the student from the waitlist
        $removeWaitlistSql = "DELETE FROM waitlist WHERE waitlistID = ?";
        $removeWaitlistStmt = $conn->prepare($removeWaitlistSql);
        $removeWaitlistStmt->bind_param("i", $waitlistID);
        $removeWaitlistStmt->execute();
        $removeWaitlistStmt->close();

        echo "<script>alert('Student removed from the waitlist successfully.');</script>";
    }

    echo "<script>window.location.href = 'enrollment.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Management</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="header text-center">
        <h1>University Global Campus</h1>
    </div>

    <!-- This enrollment section is visible to only students) -->
    <?php if ($role === 'Student'): ?>
        <div class="form-container">
            <h2 class="text-center">Enroll in a Course</h2>
            <div class="instructions mb-5">
                <p>To enroll in a course, select the course from the dropdown menu and click the "Enroll" button. To drop a course, select the course and click the "Remove" button.</p>
            </div>
            <form method="POST" id="enrollForm" class="mb-4">
                <div class="form-group">
                    <label for="courseID">Enroll or Drop a Course:</label>
                    <select name="courseID" id="courseID" class="form-control" required>
                        <option value="" disabled selected>Select a course</option>
                        <?php
                        // Fetch available courses
                        $courseSql = "SELECT courseID, name, capacity, enrolled FROM courses";
                        $courseResult = $conn->query($courseSql);
                        while ($course = $courseResult->fetch_assoc()) {
                            echo "<option value='" . $course['courseID'] . "'>" . htmlspecialchars($course['name']) . " (Enrolled: " . $course['enrolled'] . "/" . $course['capacity'] . ")</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" name="action" value="enroll" class="btn btn-primary" onclick="return confirm('Are you sure you want to enroll in this course?')">Enroll</button>
                    <button type="submit" name="action" value="remove" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this course?')">Remove</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Display enrolled courses for Admin -->
    <?php if ($is_admin): ?>
        <div class="form-container">
            <div><h2 class="text-center">Enrolled Students Management</h2></div>
            
            <?php
            // Query to get enrolled students and their courses
            $adminQuery = "
                SELECT 
                    r.registrationID, 
                    u.name AS studentName, 
                    c.name AS courseName 
                FROM registrations r
                JOIN students s ON r.studentID = s.studentID
                JOIN users u ON s.userID = u.userID
                JOIN courses c ON r.courseID = c.courseID
                ORDER BY c.name, u.name
            ";
            $adminResult = $conn->query($adminQuery);

            if ($adminResult->num_rows > 0) {
                echo "<table class='table table-bordered mt-4'>
                        <tr class='bg-secondary text-white'>
                            <th>Student Name</th>
                            <th>Course Name</th>
                            <th>Action</th>
                        </tr>";

                while ($row = $adminResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['studentName']) . "</td>
                            <td>" . htmlspecialchars($row['courseName']) . "</td>
                            <td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='registrationID' value='" . $row['registrationID'] . "'>
                                    <button type='submit' name='removeStudent' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to remove this student from the course?\")'>Remove</button>
                                </form>
                            </td>
                          </tr>";
                }

                echo "</table>";
            } else {
                echo "<p class='text-center'>No students are currently enrolled in any courses.</p>";
            }

            // Display students on the waiting list
            $waitlistQuery = "
                SELECT 
                    w.waitlistID, 
                    u.name AS studentName, 
                    c.name AS courseName, 
                    w.position 
                FROM waitlist w
                JOIN students s ON w.studentID = s.studentID
                JOIN users u ON s.userID = u.userID
                JOIN courses c ON w.courseID = c.courseID
                ORDER BY c.name, w.position
            ";
            $waitlistResult = $conn->query($waitlistQuery);

            if ($waitlistResult->num_rows > 0) {
                echo "<div class='mt-5'><h2 class='text-center'>Waitlisted Students Management</h2></div>";
                echo "<table class='table table-bordered mt-4'>
                        <tr class='bg-secondary text-white'>
                            <th>Student Name</th>
                            <th>Course Name</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>";

                while ($row = $waitlistResult->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['studentName']) . "</td>
                            <td>" . htmlspecialchars($row['courseName']) . "</td>
                            <td>" . htmlspecialchars($row['position']) . "</td>
                            <td>
                                <form method='POST' style='display:inline;'>
                                    <input type='hidden' name='waitlistID' value='" . $row['waitlistID'] . "'>
                                    <button type='submit' name='removeStudent' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to remove this student from the waitlist?\")'>Remove</button>
                                </form>
                            </td>
                          </tr>";
                }

                echo "</table>";
            } else {
                echo "<p class='text-center'>No students are currently on the waitlist.</p>";
            }
            ?>
        </div>
	    <div class="links mt-4 text-center">
        	<a href="profile.php">Back to Profile</a>
    	</div>
    <?php endif; ?>

    <!-- Link to Profile Page -->
    <div class="links mt-4 text-center">
        <a href="profile.php">Back to Profile</a>
    </div>
</div>
    <footer class="footer text-center">
        <div class="container">
            <span>&copy; 2024 Denver Doran | Course Registration System | WK-4 CST499.</span>
        </div>
    </footer>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>
