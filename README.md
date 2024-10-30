# course-registration
This README document that provides step-by-step instructions on setting up your course registration application using XAMPP and importing the SQL file to create tables with pre-enrolled students.
This repository contains a PHP-based course registration application that allows students to enroll in courses. It is designed to run on a local server environment, using XAMPP to manage the server and database.
Before you begin, ensure you have the following installed: XAMPP https://www.apachefriends.org/index.html 

1. Follow these steps to set up and run the application on your local machine.
If you have Git installed, clone the repository to your local machine or you can download the ZIP file of the repository from GitHub and extract it.

2. Move Project Files to XAMPP
After cloning or downloading the repository, move the project folder to your XAMPP `htdocs` directory, typically located at: C:\xampp\htdocs\
Rename the folder to something simple, such as `course_registration`.

3. Start XAMPP
1. Open XAMPP and start Apache and MySQL modules.
2. Ensure both services are running by ensuring both are green in XAMPP’s control panel.

4. Create the Database
1. Open your web browser and go to phpMyAdmin (http://localhost/phpmyadmin).
2. In phpMyAdmin, click on the Databases tab.
3. Under Create database, enter `course_registration` as the database name and click Create.

5. Import the SQL File
1. In phpMyAdmin, select the `course_registration` database you just created.
2. Click on the Import tab.
3. Click Choose File and select the SQL file from this repository that contains the database structure and initial data (`SQL Script to create database tables.sql`).
4. Click Go to import the file. This will create the necessary tables and insert initial data, including sample courses and enrolled students.

6. Configure Database Connection
1. Open the `database.php` file in the project folder (`htdocs/course_registration`).
2. Update the database connection settings if necessary, in the database.php:
    ```php
    $host = 'localhost';
    $username = 'root'; // Default XAMPP username
    $password = '';     // Default XAMPP password is empty
    $dbname = 'course_registration';
    ```
3. Save the changes.

7. Run the Application
1. Open your web browser and go to http://localhost/course_registration (http://localhost/course_registration).

2. You should see the homepage of the course registration application.

8. Additional Notes
1.	You can manage users and enrollments directly in phpMyAdmin by viewing the `users`, `students`, and `registrations` tables.
2.	You can also edit `styles.css` in the project folder to change the visual design.

License: This project is licensed under the MIT License. 

