# CalorEat – README
CalorEat is a web-based Hospital Meal Recommendation System developed as a Final Year Project using PHP, MySQL, JavaScript, HTML, and CSS.

## Project Description
The system is designed to assist nutritionist in managing patients and recommending suitable meals based on calorie intake and patient information.

## Main Features
- Calorie calculation
- Patient meal recommendation
- Meal customization
- User authentication
- Role-based access for Nutritionist, Patient, and Admin
- Patient management
- Meal management

## Technologies Used
- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP
- phpMyAdmin

## User Roles
### Nutritionist
Nutritionist can manage patients, calculate daily calorie intake, recommend meals, and customize meals based on patient requirements.

### Patient
Patients can view their recommended meals and account information.

### Admin
Administrators can manage user accounts and meal information.

## Installation
1. Install XAMPP
2. Place the project folder inside the 'htdocs' directory.
3. Start Apache and MySQL from XAMPP.
4. Import the provided database file into phpMyAdmin.
5. Configure the database connection.
6. Open the project through 'localhost' in web browser.

## Final Year Project
This project was developed as part o the Diploma in Information Technology Final Year Project at SEGi College Subang Jaya.

---

## Requirements
Before running the system, make sure the following are installed:
- XAMPP
- PHP
- MySQL / phpMyAdmin
- A web browser
- Composer, if the vendor folder is not included


## Setup Instructions
1. Copy the CalorEat project folder into: C:\xampp\htdocs\
2. Open the XAMPP Control Panel.
3. Start:
- Apache
- MySQL
4. Open phpMyAdmin: http://localhost/phpmyadmin
5. Create a database and import the SQL file provided with the project.
6. Open db_connect.php and make sure the database settings are correct.

## Example:
$host = "localhost";
$username = "root";
$password = "";
$database = "caloreat_database";

7. If the vendor folder is not included, open a terminal inside the project folder and run: composer install
8. Configure the SMTP email settings used for OTP password reset.
Do not include the real email application password when submitting or sharing the project.

Run the System
Open the following address in a browser: http://localhost/caloreat/login.php
Change CalorEat if your project folder uses a different name.

## Important Notes
- Apache and MySQL must be running before opening the system.
- The database must be imported before logging in.
- Internet access is required for sending OTP emails.
- Use test data only and do not store real patient information.
- Update the database name and file paths if they are different in the final project.
