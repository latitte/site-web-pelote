<?php
// Connexion à la base de données
include("../logiciel/assets/extract_parametre.php");
$prix_1seriePar_joueur = $parametres['prix_1serie/joueur'];
$prix_2PlusseriePar_joueur = $parametres['prix_2+serie/joueur'];

// Crée une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifie la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifie si la table 'joueurs' contient déjà des lignes
$sql_check_existing = "SELECT COUNT(*) AS count FROM joueurs";
$check_result = $conn->query($sql_check_existing);
$row = $check_result->fetch_assoc();

if ($row['count'] > 0) {
    echo "Le script ne peut pas être exécuté car des lignes existent déjà dans la table 'joueurs'.";
    exit();
}

// Récupère les informations des joueurs à partir de la table inscriptions
$sql = "SELECT id, `Joueur 1`, `Joueur 2` FROM inscriptions";
$result = $conn->query($sql);

$players = [];  // Tableau pour stocker les joueurs et compter leurs apparitions

if ($result->num_rows > 0) {
    // Parcourt chaque ligne de résultat
    while($row = $result->fetch_assoc()) {
        $id_equipe = $row['id'];
        $joueur1 = trim($row['Joueur 1']);
        $joueur2 = trim($row['Joueur 2']);

        // Ajoute les joueurs au tableau avec gestion des erreurs de frappe
        addPlayer($players, $joueur1, $id_equipe);
        addPlayer($players, $joueur2, $id_equipe);
    }

    // Insertion des joueurs dans la table 'joueurs'
    $nb_joueurs_1s = 0;
    $nb_joueurs_2PlusS = 0;

    foreach ($players as $joueur => $info) {
        $id_equipe = $info['id_equipe'];
        $count = $info['count'];
        $montant = $count > 1 ? $prix_2PlusseriePar_joueur : $prix_1seriePar_joueur;

        // Insère le joueur
        $sql_insert = "INSERT INTO joueurs (id_equipe, joueur, montant, status_paiement)
                       VALUES ('$id_equipe', '$joueur', $montant, 0)";
        if ($conn->query($sql_insert) === TRUE) {
            echo "$joueur ajouté avec succès avec un montant de $montant.<br>";
        } else {
            echo "Erreur lors de l'ajout de $joueur : " . $conn->error . "<br>";
        }

        // Mise à jour des compteurs
        if ($montant == $prix_1seriePar_joueur) {
            $nb_joueurs_1s++;
        } elseif ($montant == $prix_2PlusseriePar_joueur) {
            $nb_joueurs_2PlusS++;
        }
    }

    // Affiche les résultats finaux
    echo "<br>Nombre de joueurs avec un montant de $prix_1seriePar_joueur: $nb_joueurs_1s<br>";
    echo "Nombre de joueurs avec un montant de $prix_2PlusseriePar_joueur: $nb_joueurs_2PlusS<br>";

} else {
    echo "Aucun enregistrement trouvé dans la table inscriptions";
}

// Ferme la connexion
$conn->close();

/**
 * Ajoute un joueur au tableau en tenant compte des erreurs de frappe.
 *
 * @param array $players Tableau des joueurs
 * @param string $joueur Nom du joueur
 * @param int $id_equipe ID de l'équipe
 */
function addPlayer(&$players, $joueur, $id_equipe) {
    if (empty($joueur)) {
        return;
    }

    // Normalisation du nom
    $joueur_normalized = strtolower(trim($joueur));
    $joueur_parts = explode(' ', $joueur_normalized);

    // Cherche un joueur similaire dans le tableau
    foreach ($players as $existing_joueur => $info) {
        $existing_joueur_normalized = strtolower(trim($existing_joueur));
        $existing_joueur_parts = explode(' ', $existing_joueur_normalized);

        // Vérifie les permutations possibles
        foreach (permutations($existing_joueur_parts) as $existing_permutation) {
            if (levenshtein($joueur_normalized, implode(' ', $existing_permutation)) <= 1) {
                // Mise à jour de l'apparition du joueur similaire
                $players[$existing_joueur]['count']++;
                $players[$existing_joueur]['id_equipe'] = $id_equipe; // Met à jour l'id de l'équipe
                return;
            }
        }
    }

    // Ajoute un nouveau joueur si aucun joueur similaire n'est trouvé
    $players[$joueur] = ['id_equipe' => $id_equipe, 'count' => 1];
}

/**
 * Génère toutes les permutations possibles d'un tableau.
 *
 * @param array $array Tableau à permuter
 * @return array Liste des permutations
 */
function permutations($array) {
    $result = [];

    // Fonction récursive pour générer les permutations
    $permute = function($arr, $start, $end) use (&$result, &$permute) {
        if ($start == $end) {
            $result[] = $arr;
            return;
        }
        for ($i = $start; $i <= $end; $i++) {
            $arr = swap($arr, $start, $i);
            $permute($arr, $start + 1, $end);
            $arr = swap($arr, $start, $i); // Backtrack
        }
    };

    $permute($array, 0, count($array) - 1);
    return $result;
}

/**
 * Échange deux éléments d'un tableau.
 *
 * @param array $array Tableau
 * @param int $i Index 1
 * @param int $j Index 2
 * @return array Tableau modifié
 */
function swap($array, $i, $j) {
    $temp = $array[$i];
    $array[$i] = $array[$j];
    $array[$j] = $temp;
    return $array;
}
?>
