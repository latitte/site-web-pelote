<?php
// Connexion à la base de données MySQL
$servername = "mysql-tittdev.alwaysdata.net";
$username = "tittdev";
$password = "titi64120$";
$dbname = "tittdev_bdd";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}

// Récupérer les équipes de la série 'Féminine'
$sql = "SELECT * FROM classement WHERE serie = 'Féminine' ORDER BY place";
$result = $conn->query($sql);

$phase = '';
$parties = [];
$equipes = [
    'A' => [
        'poules' => [],
        '1_8' => [],
        '1_4' => [],
        '1_2' => [],
        'finale' => []
    ],
    'B' => [
        'poules' => [],
        '1_8' => [],
        '1_4' => [],
        '1_2' => [],
        'finale' => []
    ]
];

if ($result->num_rows > 0) {
    // Classifier les équipes selon leur niveau et tableau
    while ($row = $result->fetch_assoc()) {
        $niveau = $row['niveau'];
        $tableau = substr($niveau, -1); // Prendre la dernière lettre pour le tableau (A ou B)
        $phase_key = '';

        if ($niveau[0] == '1') {
            $phase_key = 'poules';
        } elseif ($niveau[0] == '3') {
            $phase_key = '1_8';
        } elseif ($niveau[0] == '4') {
            $phase_key = '1_4';
        } elseif ($niveau[0] == '5') {
            $phase_key = '1_2';
        } elseif ($niveau[0] == '6') {
            $phase_key = 'finale';
        }

        if ($phase_key) {
            $equipes[$tableau][$phase_key][] = $row;
        }
    }
    
    // Fonction pour générer les parties
    function genererParties($equipes) {
        $nb_equipes = count($equipes);
        $parties = [];
        $i = 0;
        $j = $nb_equipes - 1;
        
        while ($i < $j) {
            $parties[] = [
                'numero1' => $equipes[$i]['id'],
                'equipe1' => $equipes[$i]['joueurs'] ?? 'Inconnu',
                'numero2' => $equipes[$j]['id'],
                'equipe2' => $equipes[$j]['joueurs'] ?? 'Inconnu'
            ];
            $i++;
            $j--;
        }
        
        return $parties;
    }
    
    // Générer les parties pour chaque phase si les équipes sont disponibles
    $parties = [];
    foreach ($equipes as $tableau => $phases) {
        foreach ($phases as $phase_key => $phase_equipes) {
            if (!empty($phase_equipes)) {
                $parties[$tableau][$phase_key] = genererParties($phase_equipes);
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phase d'Élimination</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        body { font-family: Arial, sans-serif; }
        h2 { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Phase d'Élimination</h1>

    <!-- Affichage des parties pour chaque tableau et phase -->
    <?php foreach ($parties as $tableau => $phases): ?>
        <h2>Tableau <?php echo htmlspecialchars($tableau, ENT_QUOTES, 'UTF-8'); ?></h2>

        <?php foreach ($phases as $phase_key => $phase_parties): ?>
            <?php if (!empty($phase_parties)): ?>
                <h3><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $phase_key)), ENT_QUOTES, 'UTF-8'); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>Match</th>
                            <th>Équipe 1</th>
                            <th>Numéro 1</th>
                            <th>Équipe 2</th>
                            <th>Numéro 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($phase_parties as $index => $partie): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($partie['equipe1'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($partie['numero1'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($partie['equipe2'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($partie['numero2'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune équipe trouvée pour la phase <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $phase_key)), ENT_QUOTES, 'UTF-8'); ?>.</p>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</body>
</html>
