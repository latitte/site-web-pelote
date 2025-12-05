<?php
include("../../logiciel/assets/conn_bdd.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teamCode = $_POST['teamCode'];
    $sql = "SELECT * FROM inscriptions WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $teamCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            "success" => true,
            "joueur1" => $row["Joueur 1"],
            "joueur2" => $row["Joueur 2"],
            "numequipe" => $row["id"]
        );
    } else {
        $response = array("success" => false, "message" => "Aucune équipe trouvée avec ce code.");
    }
    $stmt->close();
} else {
    $response = array("success" => false, "message" => "Requête non valide.");
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
