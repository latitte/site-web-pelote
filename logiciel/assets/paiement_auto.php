<?php
include '../assets/conn_bdd.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Étape 1 : Récupérer tous les joueurs
$stmt = $pdo->query("SELECT id_equipe, status_paiement FROM joueurs");
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Étape 2 : Grouper les joueurs par racine d'équipe
$groupes_equipes = [];

foreach ($joueurs as $joueur) {
    $parts = explode('.', $joueur['id_equipe']);
    $racines = [];

    if (isset($parts[0])) $racines[] = intval($parts[0]);
    if (isset($parts[1]) && $parts[1] !== '00') $racines[] = intval($parts[1]);

    foreach ($racines as $racine) {
        if (!isset($groupes_equipes[$racine])) {
            $groupes_equipes[$racine] = [];
        }
        $groupes_equipes[$racine][] = $joueur;
    }
}

// Étape 3 : Pour chaque groupe, si 2 joueurs ont payé, mettre à jour `paye = 1` dans `inscriptions`
$compteur = 0;
foreach ($groupes_equipes as $id_inscription => $joueurs_groupe) {
    $payes = array_filter($joueurs_groupe, fn($j) => $j['status_paiement'] == 1);
    if (count($payes) >= 2) {
        // Mise à jour dans la table inscriptions
        $update = $pdo->prepare("UPDATE inscriptions SET paye = 1 WHERE id = ?");
        $update->execute([$id_inscription]);
        $compteur++;
    }
}

echo "✅ $compteur équipe(s) mise(s) à jour dans `inscriptions` avec paye = 1.";
    echo '<script>window.location.href = "../edit_paiement.php?success=1";</script>';
    exit;

?>
