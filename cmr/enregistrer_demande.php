<?php
require_once("../logiciel/assets/conn_bdd_lecteur.php");

// Connexion à la base
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Erreur de connexion : " . $conn->connect_error);
}

// Préparer la requête
$stmt = $conn->prepare("
  INSERT INTO demandes_tournoi 
    (nom, prenom, email, telephone, nom_tournoi, joueurs, duree_mois, prix_inscription, arbitres, admins, description, prix_total, url_tournoi)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
  die("Erreur de préparation : " . $conn->error);
}

$stmt->bind_param(
  "sssssiididssd",
  $_POST['nom'],
  $_POST['prenom'],
  $_POST['email'],
  $_POST['telephone'],
  $_POST['nom_tournoi'],
  $_POST['joueurs'],
  $_POST['duree_mois'],
  $_POST['prix_inscription'],
  $_POST['arbitres'],
  $_POST['admins'],
  $_POST['description'],
  $_POST['prix_total'],
  $_POST['url_tournoi']
);

$success = $stmt->execute();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Confirmation de demande</title>
  <style>
    body {
      background-color: #f2f2f7;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .confirmation {
      background-color: #fff;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      text-align: center;
    }

    .confirmation h1 {
      color: #007aff;
      font-size: 26px;
      margin-bottom: 20px;
    }

    .confirmation p {
      font-size: 18px;
      margin-bottom: 30px;
      color: #333;
    }

    .btn-back {
      background-color: #007aff;
      color: white;
      padding: 12px 24px;
      font-size: 16px;
      border: none;
      border-radius: 10px;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    .btn-back:hover {
      background-color: #005bb5;
    }
  </style>
</head>
<body>
  <div class="confirmation">
    <h1><?php echo $success ? '✅ Demande enregistrée' : '❌ Erreur lors de l\'enregistrement'; ?></h1>
    <p>
      <?php echo $success
        ? "Merci pour ta demande, je te recontacte rapidement pour créer ton tournoi !"
        : "Une erreur est survenue. Merci de réessayer ou de me contacter directement."; ?>
    </p>
    <a href="./" class="btn-back">⬅ Retour</a>
  </div>
</body>
</html>
