<?php

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'p') {
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

// Traiter le formulaire d'alerte d'urgence
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['type_urgence']) || !isset($_POST['description'])) {
        die("Tous les champs doivent être remplis.");
    }

    // Récupérer les données du formulaire
    $type_urgence = $_POST['type_urgence'];  // Emergency Type
    $description = $_POST['description'];  // Description
    $date_signalement = date('Y-m-d H:i:s');  // Current Date and Time

    // Fetch the patient ID and name
    $sqlmain = "SELECT * FROM patient WHERE pemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pid"];
    $username = $userfetch["pname"];  // Optional: You can use the patient's name if needed

    // Send the alert to all doctors with specialties = 1
    $sql = "SELECT docid FROM doctor WHERE specialties = 1";
    $result = $database->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $docid = $row['docid'];  // Correctly get the doctor ID

            // Insertion of the emergency alert for each doctor
            $sql = "INSERT INTO emergency (pid, docid, type_urgence, description, date_signalement) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("sssss", $userid, $docid, $type_urgence, $description, $date_signalement);

            if ($stmt->execute()) {
                // Optionally: send email to the doctor (you can skip this part if you don't need it)
            }
        }
    }

    // Redirection vers la page d'accueil ou autre page après l'envoi de l'alerte
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Signaling</title>
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
            max-width: 600px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Emergency Signaling</h1>
        <form action="Emergency_Alert.php" method="POST">
            <label for="type_urgence">Emergency Type:</label>
            <input type="text" id="type_urgence" name="type_urgence" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <input type="submit" value="Send Alert">
        </form>
    </div>
</body>

</html>