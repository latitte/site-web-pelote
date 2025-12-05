<?php
date_default_timezone_set('Europe/Paris');

echo "===== SCRIPT DEBUG DEMARRÉ =====\n\n";

// Connexion à la base master
include("../../master/conn_bdd_master.php");
echo "🔌 Connexion à la base MASTER : $dbname\n";

$master_conn = new mysqli($servername, $username, $password, $dbname);
if ($master_conn->connect_error) {
    die("❌ Erreur connexion MASTER : " . $master_conn->connect_error . "\n");
}

echo "✅ Connexion MASTER OK\n\n";

// Récupération des tournois
$sql = "SELECT nom_off, nom_log, lien FROM tournoi";
echo "📄 Requête tournois : $sql\n";

$tournois = $master_conn->query($sql);

if (!$tournois) {
    die("❌ Erreur requête tournois : " . $master_conn->error . "\n");
}

if ($tournois->num_rows === 0) {
    die("❌ Aucun tournoi trouvé dans la base MASTER.\n");
}

echo "📌 Tournois trouvés : {$tournois->num_rows}\n\n";

$total_tournoi = 0;
$total_demandes = 0;

while ($row = $tournois->fetch_assoc()) {
    echo "----------------------------------------\n";
    echo "🎯 Traitement du tournoi :\n";
    print_r($row);
    echo "----------------------------------------\n";

    $nom = $row['nom_log'];
    $lien = $row['lien'];

    echo "🔄 Connexion à la base du tournoi : tittdev_$nom\n";

    // Connexion à la base du tournoi
    $bdd_name = "tittdev_" . $nom;
    $tournoi_conn = new mysqli($servername, $username, $password, $bdd_name);

    if ($tournoi_conn->connect_error) {
        echo "❌ Erreur connexion DB $bdd_name : " . $tournoi_conn->connect_error . "\n\n";
        continue;
    }

    echo "✅ Connexion à $bdd_name OK\n";

    // Date limite = 24h
    $limite = (new DateTime())->sub(new DateInterval('PT24H'))->format('Y-m-d H:i:s');
    echo "⏰ Date limite (24h avant) = $limite\n";

    // Requête de récupération
    $sql_demande = "
        SELECT token FROM demandes_report
        WHERE statut = 'en_attente' AND horodatage <= ?
    ";
    echo "📄 Requête demandes : $sql_demande\n";

    $stmt = $tournoi_conn->prepare($sql_demande);
    if (!$stmt) {
        echo "❌ Erreur préparation SQL : " . $tournoi_conn->error . "\n\n";
        continue;
    }

    $stmt->bind_param("s", $limite);
    $stmt->execute();
    $res = $stmt->get_result();

    echo "🔍 Nombre de demandes trouvées : {$res->num_rows}\n";

    $nb = 0;

    while ($demande = $res->fetch_assoc()) {
        echo "➡️ Demande trouvée : ";
        print_r($demande);

        $token = $demande['token'];
        $url = "$lien/client/valider_report.php?token=$token&action=refuse";

        echo "🌐 Appel URL : $url\n";

        // Affiche les warnings HTTP
        $context = stream_context_create([
            'http' => ['ignore_errors' => true]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            echo "❌ Erreur file_get_contents()\n";
        } else {
            echo "📨 Réponse serveur :\n$response\n";
            echo "----------------------------------------\n";
            $nb++;
        }
    }

    echo "✅ Total refusés pour $nom : $nb\n\n";

    $total_demandes += $nb;
    $total_tournoi++;

    $tournoi_conn->close();
}

$master_conn->close();

echo "\n===== FIN DU SCRIPT =====\n";
echo "🎯 Résultat final : $total_tournoi tournoi(s), $total_demandes demande(s) refusée(s).\n";
?>
