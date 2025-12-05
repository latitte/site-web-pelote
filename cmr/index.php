<?php
if ($_SERVER['HTTP_HOST'] !== 'tournoi-pelote.com' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    header('HTTP/1.1 403 Forbidden');
    exit('Accès interdit. Cette page est uniquement accessible depuis 
        <a href="https://tournoi-pelote.com/cmr/">tournoi-pelote.com</a>.');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tournoi Pelote</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background-color: #ffffff;
      color: #111;
    }
    header {
      padding: 40px 20px;
      text-align: center;
    }
    header h1 {
      font-size: 3em;
      margin-bottom: 10px;
    }
    header p {
      font-size: 1.2em;
      color: #555;
    }
    section {
      padding: 40px 20px;
      max-width: 900px;
      margin: auto;
    }
    .features {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }
    .feature {
      background: #f7f7f7;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .feature h3 {
      margin-top: 0;
      font-size: 1.2em;
    }
    .cta {
      text-align: center;
      margin-top: 60px;
    }
    .cta a {
      text-decoration: none;
      background: #000;
      color: #fff;
      padding: 14px 28px;
      border-radius: 999px;
      font-weight: 600;
      font-size: 1em;
      transition: background 0.3s ease;
    }
    .cta a:hover {
      background: #333;
    }
    .info-contact {
      margin-top: 60px;
      text-align: center;
      font-size: 1em;
      color: #444;
    }
    .info-contact a {
      color: #007aff;
      text-decoration: none;
    }
    .info-contact a:hover {
      text-decoration: underline;
    }
    .tournois {
      margin-top: 40px;
      background: #f1f1f1;
      padding: 20px;
      border-radius: 12px;
    }
    .tournois h2 {
      font-size: 1.4em;
      margin-bottom: 10px;
    }
    .about-local {
      margin-top: 40px;
      background: #e9f5ff;
      padding: 20px;
      border-radius: 12px;
      color: #003366;
    }
    .about-local h2 {
      font-size: 1.4em;
      margin-bottom: 10px;
    }
    footer {
      text-align: center;
      padding: 20px;
      color: #aaa;
      font-size: 0.9em;
    }
    @media (max-width: 768px) {
      .features {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Tournoi Pelote</h1>
    <p>L'outil moderne pour gérer vos tournois de pelote basque, simplement et efficacement.</p>
  </header>

  <section>
    <div class="features">
      <div class="feature">
        <h3>🗓️ Planification simplifiée</h3>
        <p>Créez des plannings de matchs en quelques clics avec une interface intuitive.</p>
      </div>
      <div class="feature">
        <h3>🎯 Suivi des scores</h3>
        <p>Mettez à jour les scores en direct et offrez une expérience immersive aux participants et spectateurs.</p>
      </div>
      <div class="feature">
        <h3>📱 Interface mobile</h3>
        <p>Optimisé pour les smartphones, pour les joueurs comme pour les organisateurs.</p>
      </div>
      <div class="feature">
        <h3>🧠 Design intuitif</h3>
        <p>Inspiré par Apple, avec une expérience fluide et minimaliste.</p>
      </div>
    </div>
    <div class="cta">
      <a href="./simulateur_tarif.php">Découvrir les prix</a>
    </div>

    <div class="tournois">
      <h2>🏆 Ils nous font déjà confiance</h2>
      <ul>
        <li>Tournoi d'Ilharre Hiver 2024</li>
        <li>Tournoi d'Ilharre Eté 2025</li>
        <li>Tournoi d'Ilharre Hiver 2025</li>

      </ul>
    </div>

    <div class="about-local">
      <h2>👨‍💻 Développé localement</h2>
      <p>Tournoi Pelote est un projet conçu et développé par un étudiant passionné d'informatique, originaire du Pays Basque.</p>
      <p>L'outil est 100% local, pensé pour les clubs et associations de la région, avec une réactivité maximale en cas de besoin.</p>
      <p>Besoin d'une modification ou d'un coup de main pendant votre tournoi ? Une réponse rapide est garantie.</p>
    </div>

    <div class="info-contact">
      <p>📧 Contact : <a href="mailto:contact@tournoi-pelote.com">admin@tournoi-pelote.com</a></p>
      <p>📸 Suivez-nous sur <a href="https://www.instagram.com/tournoi_pelote/" target="_blank">Instagram</a></p>
    </div>
  </section>

  <footer>
    © 2025 Tournoi Pelote. Tous droits réservés.
  </footer>
</body>
</html>