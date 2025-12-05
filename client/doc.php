<?php
include("../logiciel/assets/extract_parametre.php");
$text_accueil = $parametres['text_accueil'];
$img_accueil = $parametres['img_accueil'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOURNOI ILHARRE - PALA TXAPELKETA 2024</title>
    <style>
        :root {
            --background: #f0f0f5;
            --foreground: #333333;
            --primary: #007aff;
            --border: #d1d1d6;
            --input: #ffffff;
            --ring: #007aff;
            --primary-foreground: #ffffff;
            --secondary: #8e8e93;
            --accent: #34c759;
            --destructive: #ff3b30;
            --muted: #aeaeb2;
            --card: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "San Francisco", "Helvetica Neue", Arial, sans-serif;
            background-color: var(--background);
            color: var(--foreground);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;

        }

        .subtitle {
            font-size: 18px;

        }

        .content {
            margin-top: 20px;
        }

        h2 {
            font-size: 20px;

        }

        ul {
            list-style-type: disc;
            margin: 10px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .important {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../logiciel/assets/img/<?php echo $img_accueil; ?>" alt="TOURNOI ILHARRE - PALA TXAPELKETA 2024">

        </div>
        <div class="content">
            <?php echo $text_accueil; ?>
        </div>
    </div>
</body>

<footer>

<?php include("./assets/footer.php"); ?>


</footer>


</html>
