<?php
include("./assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion tournoi : " . $conn->connect_error);
}

include("../master/conn_bdd_master.php");
$conn_master = new mysqli($servername, $username, $password, $dbname);
if ($conn_master->connect_error) {
    die("Erreur de connexion master : " . $conn_master->connect_error);
}

// Nom du tournoi = sous-domaine
$tournoi_nom = $var_tournoi;

// Récupération des inscriptions
$inscriptions = $conn->query("SELECT * FROM inscriptions");
if (!$inscriptions) die("Erreur chargement inscriptions : " . $conn->error);

// Récupération du calendrier
$calendrier = $conn->query("SELECT * FROM calendrier");
if (!$calendrier) die("Erreur chargement calendrier : " . $conn->error);

// Traitement des matchs
$matchs = [];
while ($row = $calendrier->fetch_assoc()) {
    if (!$row['partie_jouee']) continue;
    $equipes = explode("/", $row['partie']);
    $score = $row['score'];
    $commentaire = strtolower($row['commentaire']);

    foreach ($equipes as $id_equipe) {
        $id_equipe = intval($id_equipe);
        if (!isset($matchs[$id_equipe])) {
            $matchs[$id_equipe] = ['joue' => 0, 'gagne' => 0, 'report' => 0];
        }
        $matchs[$id_equipe]['joue']++;

        if (str_contains($commentaire, 'report') || str_contains($commentaire, 'décal')) {
            $matchs[$id_equipe]['report']++;
        }

        if ($score && !str_contains(strtolower($score), "forfait")) {
            if (
                ($id_equipe == intval($equipes[0]) && strpos($score, '-') === false) ||
                ($id_equipe == intval($equipes[1]) && strpos($score, '-') !== false)
            ) {
                $matchs[$id_equipe]['gagne']++;
            }
        }
    }
}

// Base des joueurs
$joueurs_data = [];

$inscriptions->data_seek(0);
while ($equipe = $inscriptions->fetch_assoc()) {
    $id_equipe = intval($equipe['id']);
    $serie = $equipe['serie'];
    $stats = $matchs[$id_equipe] ?? ['joue' => 0, 'gagne' => 0, 'report' => 0];
    $report_pct = $stats['joue'] > 0 ? round(($stats['report'] / $stats['joue']) * 100) : 0;

    $joueurs = [
        ['nom_complet' => $equipe['Joueur 1'], 'equipier' => $equipe['Joueur 2']],
        ['nom_complet' => $equipe['Joueur 2'], 'equipier' => $equipe['Joueur 1']],
    ];

    foreach ($joueurs as $joueur) {
        $parts = explode(" ", trim($joueur['nom_complet']));
        $prenom = array_pop($parts);
        $nom = implode(" ", $parts);
        $cle = strtolower($nom . '_' . $prenom);

        if (!isset($joueurs_data[$cle])) {
            $joueurs_data[$cle] = [
                'nom' => $nom,
                'prenom' => $prenom,
                'partie' => 0,
                'win' => 0,
                'report_total' => 0,
                'equipiers' => [],
                'series' => [],
            ];
        }

        $joueurs_data[$cle]['partie'] += $stats['joue'];
        $joueurs_data[$cle]['win'] += $stats['gagne'];
        $joueurs_data[$cle]['report_total'] += $stats['report'];

        if (!in_array($joueur['equipier'], $joueurs_data[$cle]['equipiers'])) {
            $joueurs_data[$cle]['equipiers'][] = $joueur['equipier'];
        }
        if (!in_array($serie, $joueurs_data[$cle]['series'])) {
            $joueurs_data[$cle]['series'][] = $serie;
        }
    }
}

// Insertion ou mise à jour finale
foreach ($joueurs_data as $joueur) {
    $nom = $joueur['nom'];
    $prenom = $joueur['prenom'];
    $equipiers_str = implode(", ", $joueur['equipiers']);
    $series_str = implode(", ", $joueur['series']);
    $partie_total = $joueur['partie'];
    $win_total = $joueur['win'];
    $report_total = $joueur['report_total'];
    $report_pct = $partie_total > 0 ? round(($report_total / $partie_total) * 100) : 0;

    // Vérifier si le joueur existe
    $stmt_check = $conn_master->prepare("SELECT * FROM joueur WHERE nom = ? AND prenom = ?");
    $stmt_check->bind_param("ss", $nom, $prenom);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $ancien = $result->fetch_assoc();
        $all_series = array_unique(array_merge(
            explode(", ", $ancien['serie'] ?? ""),
            $joueur['series']
        ));
        $all_equipiers = array_unique(array_merge(
            explode(", ", $ancien['equipier'] ?? ""),
            $joueur['equipiers']
        ));
        $new_series = implode(", ", $all_series);
        $new_equipiers = implode(", ", $all_equipiers);

        $stmt_update = $conn_master->prepare("
            UPDATE joueur SET 
                partie = ?,
                win = ?,
                report_pourcentage = ?,
                equipier = ?,
                serie = ?,
                tournoi = ?
            WHERE nom = ? AND prenom = ?
        ");
        $stmt_update->bind_param("iiisssss", $partie_total, $win_total, $report_pct, $new_equipiers, $new_series, $tournoi_nom, $nom, $prenom);
        $stmt_update->execute();
    } else {
        // Insertion
        $stmt_insert = $conn_master->prepare("
            INSERT INTO joueur (nom, prenom, partie, win, tournoi_win, report_pourcentage, tournoi, serie, equipier)
            VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param("ssiiisss", $nom, $prenom, $partie_total, $win_total, $report_pct, $tournoi_nom, $series_str, $equipiers_str);
        $stmt_insert->execute();
    }
}

echo "✅ Fiches joueurs mises à jour avec tournoi = $tournoi_nom et séries concaténées.";
