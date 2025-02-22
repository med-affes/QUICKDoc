<?php

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'd') {
        header("location: ../login.php");
        exit;
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
    exit;
}

// Connexion à la base de données
include("../connection.php");  // Including the connection.php file

// Get doctor information based on session user
$sqlmain = "SELECT * FROM doctor WHERE docemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$docid = $userfetch["docid"];

// Fetch emergency alerts for doctors with specialties = 1
$sql = "SELECT * FROM emergency WHERE docid = ?";
$stmt = $database->prepare($sql);
$stmt->bind_param("s", $docid);
$stmt->execute();
$result = $stmt->get_result();

// Display alerts for the doctor
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Alerts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ccc;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        .alert-box {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Emergency Alerts</h1>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Fetch patient name
                $patient_sql = "SELECT pname FROM patient WHERE pid=?";
                $patient_stmt = $database->prepare($patient_sql);
                $patient_stmt->bind_param("s", $row['pid']);
                $patient_stmt->execute();
                $patient_result = $patient_stmt->get_result();
                $patient_row = $patient_result->fetch_assoc();
                $patient_name = $patient_row['pname'];

                // Display alert details
                echo "<div class='alert'>";
                echo "<h3>Patient: " . $patient_name . "</h3>";
                echo "<p><strong>Emergency Type:</strong> " . $row['type_urgence'] . "</p>";
                echo "<p><strong>Description:</strong> " . $row['description'] . "</p>";
                echo "<p><strong>Date of Alert:</strong> " . $row['date_signalement'] . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No new alerts.</p>";
        }
        ?>
    </div>
</body>

</html>