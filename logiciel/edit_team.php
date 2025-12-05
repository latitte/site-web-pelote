<?php
// Inclure la connexion à la base de données
include './assets/conn_bdd.php';

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupérer les données de la table
$sql = "SELECT * FROM inscriptions";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<form action="./assets/update_data_team.php" method="post">';
    
    // Afficher chaque enregistrement dans un formulaire
    while ($row = $result->fetch_assoc()) {
        echo '<div>';
        echo '<h3>Equipe n°' . $row['id'] .' </h3>';
        echo '<input type="hidden" name="ids[]" value="' . $row['id'] . '">';
        
        echo '<label for="Horodateur_' . $row['id'] . '">Horodateur:</label>';
        echo '<input type="text" id="Horodateur_' . $row['id'] . '" name="Horodateur[]" value="' . $row['Horodateur'] . '"><br>';
        
        echo '<label for="Joueur_1_' . $row['id'] . '">Joueur 1:</label>';
        echo '<input type="text" id="Joueur_1_' . $row['id'] . '" name="Joueur_1[]" value="' . $row['Joueur 1'] . '"><br>';

        echo '<label for="Joueur_2_' . $row['id'] . '">Joueur 2:</label>';
        echo '<input type="text" id="Joueur_2_' . $row['id'] . '" name="Joueur_2[]" value="' . $row['Joueur 2'] . '"><br>';

        echo '<label for="telephone_' . $row['id'] . '">Téléphone:</label>';
        echo '<input type="text" id="telephone_' . $row['id'] . '" name="telephone[]" value="' . $row['telephone'] . '"><br>';

        echo '<label for="serie_' . $row['id'] . '">Série:</label>';
        echo '<input type="text" id="serie_' . $row['id'] . '" name="serie[]" value="' . $row['serie'] . '"><br>';

        echo '<label for="poule_' . $row['id'] . '">Poule:</label>';
        echo '<input type="text" id="poule_' . $row['id'] . '" name="poule[]" value="' . $row['poule'] . '"><br>';

        echo '<label for="lundi_' . $row['id'] . '">Lundi:</label>';
        echo '<input type="text" id="lundi_' . $row['id'] . '" name="lundi[]" value="' . $row['lundi'] . '"><br>';

        echo '<label for="mardi_' . $row['id'] . '">Mardi:</label>';
        echo '<input type="text" id="mardi_' . $row['id'] . '" name="mardi[]" value="' . $row['mardi'] . '"><br>';

        echo '<label for="mercredi_' . $row['id'] . '">Mercredi:</label>';
        echo '<input type="text" id="mercredi_' . $row['id'] . '" name="mercredi[]" value="' . $row['mercredi'] . '"><br>';

        echo '<label for="jeudi_' . $row['id'] . '">Jeudi:</label>';
        echo '<input type="text" id="jeudi_' . $row['id'] . '" name="jeudi[]" value="' . $row['jeudi'] . '"><br>';

        echo '<label for="vendredi_' . $row['id'] . '">Vendredi:</label>';
        echo '<input type="text" id="vendredi_' . $row['id'] . '" name="vendredi[]" value="' . $row['vendredi'] . '"><br>';

        echo '<label for="samedi_' . $row['id'] . '">Samedi:</label>';
        echo '<input type="text" id="samedi_' . $row['id'] . '" name="samedi[]" value="' . $row['samedi'] . '"><br>';

        echo '<label for="dimanche_' . $row['id'] . '">Dimanche:</label>';
        echo '<input type="text" id="dimanche_' . $row['id'] . '" name="dimanche[]" value="' . $row['dimanche'] . '"><br>';

        echo '<label for="periodes_indispo_' . $row['id'] . '">Périodes indisponibles:</label>';
        echo '<input type="text" id="periodes_indispo_' . $row['id'] . '" name="periodes_indispo[]" value="' . $row['periodes_indispo'] . '"><br>';

        echo '<label for="code_' . $row['id'] . '">Code:</label>';
        echo '<input type="text" id="code_' . $row['id'] . '" name="code[]" value="' . $row['code'] . '"><br>';

        echo '<label for="commentaire_' . $row['id'] . '">Commentaire:</label>';
        echo '<textarea id="commentaire_' . $row['id'] . '" name="commentaire[]">' . $row['commentaire'] . '</textarea><br>';

        echo '<label for="forfait_' . $row['id'] . '">Forfait:</label>';
        echo '<input type="radio" id="forfait_oui_' . $row['id'] . '" name="forfait_' . $row['id'] . '" value="1"' . ($row['forfait'] == 1 ? ' checked' : '') . '> Oui';
        echo '<input type="radio" id="forfait_non_' . $row['id'] . '" name="forfait_' . $row['id'] . '" value="0"' . ($row['forfait'] == 0 ? ' checked' : '') . '> Non<br>';

        echo '<label for="paye_' . $row['id'] . '">Payé:</label>';
        echo '<input type="radio" id="paye_oui_' . $row['id'] . '" name="paye_' . $row['id'] . '" value="1"' . ($row['paye'] == 1 ? ' checked' : '') . '> Oui';
        echo '<input type="radio" id="paye_non_' . $row['id'] . '" name="paye_' . $row['id'] . '" value="0"' . ($row['paye'] == 0 ? ' checked' : '') . '> Non<br>';

        echo '</div>';
        echo '<hr>';
    }
    
    echo '<input type="submit" value="Enregistrer">';
    echo '</form>';
} else {
    echo "Aucun enregistrement trouvé.";
}

// Fermer la connexion
$conn->close();
?>
