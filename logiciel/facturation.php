<?php
// fichier : client/facturation.php

include("./assets/conn_bdd.php");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Erreur : ".$conn->connect_error);

// Récupération factures
$factures = $conn->query("SELECT * FROM facturation ORDER BY date_facture DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facturation Admin</title>

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

<style>
.card-apple {border-radius:18px; padding:25px; background:#fff; border:1px solid #eee; box-shadow:0 8px 18px rgba(0,0,0,0.06);}
.apple-title {font-size:28px; font-weight:700; background:linear-gradient(90deg,#000,#666); -webkit-background-clip:text; color:transparent;}
.status-badge {padding:4px 10px; border-radius:30px; font-weight:600;}
.statut-payé{background:#d4ffda; color:#1b7d2b;}
.statut-en_attente{background:#fff3bf; color:#ad8b00;}
.statut-annulé{background:#ffd4d4; color:#952020;}
</style>
</head>
<body>

<div class="d-flex">

<?php include("./assets/menu.php"); ?>

<main class="container">

    <div class="border-bottom pb-2 mb-3">
        <h1 class="h3">Vos factures</h1>
    </div>

    <p class="text-muted mb-4">
        Cliquez sur une facture pour afficher les détails.
    </p>

    <table class="table table-hover">
        <thead class="thead-light">
        <tr>
            <th>Date</th>
            <th>Libellé</th>
            <th>Montant</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody>

        <?php while ($f = $factures->fetch_assoc()): ?>
        <tr data-toggle="collapse" data-target="#facture<?php echo $f["id"]; ?>" style="cursor:pointer;">
            <td><?php echo date("d/m/Y", strtotime($f["date_facture"])); ?></td>
            <td><?php echo htmlspecialchars($f["libelle"]); ?></td>
            <td><strong><?php echo number_format($f["montant_ttc"],2,","," "); ?> €</strong></td>
            <td>
                <span class="status-badge statut-<?php echo $f["statut"]; ?>">
                    <?php echo str_replace("_"," ",$f["statut"]); ?>
                </span>
            </td>
        </tr>

        <!-- Zone détaillée -->
        <tr class="collapse" id="facture<?php echo $f["id"]; ?>">
            <td colspan="4" class="p-4" style="background:#f8f9fa;">
                <strong>Résumé :</strong><br>
                <?php echo nl2br(htmlspecialchars($f["resume_court"])); ?>

                <hr>

                <strong>Description détaillée :</strong><br>
                <?php echo nl2br(htmlspecialchars($f["description_longue"])); ?>
            </td>
        </tr>

        <?php endwhile; ?>

        </tbody>
    </table>

</main>

</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
