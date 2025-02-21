<?php 
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connexion à la base de données
    $servername = "localhost:127.0.0.1:3307";
    $username = "root"; 
    $password = ""; 
    $dbname = "edoc"; 

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("La connexion a échoué: " . $conn->connect_error);
    }

    // Récupérer les données du formulaire
    $pid = $_POST['pid'];
    $docid = $_POST['docid'];
    $type_urgence = $_POST['type_urgence'];
    $description = $_POST['description'];
    $date_signalement = date('Y-m-d H:i:s');

    // Insertion des données dans la base avec une requête préparée
    $sql = "INSERT INTO emergency (pid, docid, type_urgence, description, date_signalement) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $pid, $docid, $type_urgence, $description, $date_signalement);

    if ($stmt->execute()) {
        // Envoyer une notification aux médecins
        $sql = "SELECT docemail FROM doctor";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $to = $row['docemail']; 
                $subject = "Nouvelle Alerte d'Urgence";
                $message = "Une nouvelle alerte d'urgence a été signalée.\nType: $type_urgence\nDescription: $description.";
                $headers = "From: noreply@cabinetmedical.com";

                // Simuler l'envoi d'un email (remplace par une vraie configuration SMTP si nécessaire)
                mail($to, $subject, $message, $headers);
            }
        }
    }

    // Fermer la connexion
    $stmt->close();
    $conn->close();

    // Redirection vers la page d'accueil
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
        input[type="text"], textarea {
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
        .hover-link1 {
            background-color: #0056b3
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Emergency Signaling</h1>
        <form action="Emergency Alert.php" method="POST">
            <label for="pid">Patient Name:</label>
            <input type="text" id="pid" name="pid" required>

            <label for="docid">Doctor Name:</label>
            <input type="text" id="docid" name="docid" required>

            <label for="type_urgence">Emergency Type:</label>
            <input type="text" id="type_urgence" name="type_urgence" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <input type="submit" value="Send Alert">
            
        
        </form>
    </div>
</body>
</html>
