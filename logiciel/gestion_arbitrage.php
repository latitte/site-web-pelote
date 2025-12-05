<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="d-flex">

    <!-- MENU VERTICAL -->
    <?php include("./assets/menu.php"); ?>

    <!-- CONTENU PRINCIPAL (corrigé pour éviter le chevauchement) -->
    <main class="container-fluid"
          style="padding:30px; margin-left:250px; max-width:calc(100% - 250px);">

<?php
// Connexion
include("./assets/conn_bdd.php");
$conn = new mysqli($servername, $username, $password, $dbname);

// Chargement zones
$sql = "SELECT valeur FROM parametre WHERE parametre = 'zone_arbitre' LIMIT 1";
$result = $conn->query($sql);
$zones = [];
if ($result && ($row = $result->fetch_assoc())) {
    if (!empty($row['valeur'])) {
        $zones = json_decode($row['valeur'], true);
        if (!is_array($zones)) $zones = [];
    }
}

/* ------------ TRAITEMENT DU FORMULAIRE AVANT LE HTML ------------ */

if (isset($_POST['add_zone'])) {

    $zones[] = [
        "debut"   => $_POST['debut'],
        "fin"     => $_POST['fin'],
        "couleur" => $_POST['couleur'],
        "libelle" => trim($_POST['libelle'])
    ];

    $json = $conn->real_escape_string(json_encode($zones, JSON_UNESCAPED_UNICODE));
    $conn->query("UPDATE parametre SET valeur='$json' WHERE parametre='zone_arbitre'");

    echo "<script>window.location.href = 'gestion_arbitrage.php';</script>";
    exit();
}

if (isset($_POST['delete_zone'])) {

    $index = intval($_POST['delete_zone']);
    unset($zones[$index]);
    $zones = array_values($zones);

    $json = $conn->real_escape_string(json_encode($zones, JSON_UNESCAPED_UNICODE));
    $conn->query("UPDATE parametre SET valeur='$json' WHERE parametre='zone_arbitre'");

    echo "<script>window.location.href = 'gestion_arbitrage.php';</script>";
    exit();

}

?>


<!-- STYLE MODERNE -->
<style>
    .modern-card {
        background:#fff;
        border-radius:14px;
        padding:22px;
        margin-bottom:28px;
        box-shadow:0 4px 20px rgba(0,0,0,0.06);
        border:1px solid #e9ecef;
    }
    .modern-title {
        font-weight:700;
        font-size:1.4rem;
        margin-bottom:20px;
        color:#2c3e50;
        display:flex;
        align-items:center;
        gap:10px;
    }
    .form-control {
        border-radius:8px !important;
        padding:10px;
        border:1px solid #ced4da;
    }
    .btn-modern {
        border-radius:8px;
        padding:10px 18px;
        font-weight:600;
        background:#007bff;
        color:white;
        border:none;
    }
    .btn-modern:hover {
        background:#0056d6;
        color:white;
    }
    .color-preview {
        width:50px;
        height:22px;
        border-radius:6px;
        border:1px solid #ccc;
    }
    .zone-badge {
        display:inline-block;
        padding:4px 10px;
        border-radius:8px;
        font-size:.85rem;
        font-weight:600;
        background:#eef0f3;
        color:#34495e;
    }
    .table-modern {
        border-radius:10px;
        overflow:hidden;
        background:white;
    }
    .table-modern thead {
        background:#f8f9fa;
    }
    .table-modern td {
        vertical-align:middle !important;
    }
</style>



<!-- FORMULAIRE -->
<div class="modern-card">

    <div class="modern-title">
        <i class="fas fa-palette"></i> Ajouter une zone d’arbitrage
    </div>

    <form method="POST">

        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Date début</label>
                <input type="date" class="form-control" name="debut" required>
            </div>

            <div class="form-group col-md-3">
                <label>Date fin</label>
                <input type="date" class="form-control" name="fin" required>
            </div>

            <div class="form-group col-md-3">
                <label>Couleur</label>
                <input type="color" class="form-control" name="couleur" value="#ffb3b3">
            </div>

            <div class="form-group col-md-3">
                <label>Libellé</label>
                <input type="text" class="form-control" name="libelle" placeholder="Ex : Tournoi d’hiver">
            </div>
        </div>

        <button type="submit" name="add_zone" class="btn-modern mt-2">
            <i class="fas fa-plus-circle"></i> Ajouter
        </button>

    </form>

</div>



<!-- TABLE DES ZONES -->
<div class="modern-card">

    <div class="modern-title">
        <i class="fas fa-list"></i> Zones existantes
    </div>

<?php if (empty($zones)) : ?>
    <p style="color:#555;">Aucune zone définie.</p>

<?php else: ?>

    <table class="table table-modern table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Libellé</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Couleur</th>
                <th>Aperçu</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($zones as $i => $z): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><span class="zone-badge"><?= htmlspecialchars($z['libelle']) ?></span></td>
            <td><?= htmlspecialchars($z['debut']) ?></td>
            <td><?= htmlspecialchars($z['fin']) ?></td>
            <td><?= htmlspecialchars($z['couleur']) ?></td>
            <td>
                <div class="color-preview" style="background:<?= $z['couleur'] ?>"></div>
            </td>
            <td>
                <form method="POST">
                    <button class="btn btn-danger btn-sm"
                            onclick="return confirm('Supprimer cette zone ?');"
                            name="delete_zone" value="<?= $i ?>">
                        Supprimer
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

<?php endif; ?>

</div>



    </main> <!-- FIN MAIN -->

</div> <!-- FIN D-FLEX -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
