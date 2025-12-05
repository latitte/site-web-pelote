<?php
// Adresse email du destinataire
$to = "destinataire@example.com";

// Sujet de l'email
$subject = "Confirmation d'inscription au tournoi de pelote";

// Headers pour envoyer un email HTML avec image
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: noreply@tonsite.com" . "\r\n";

// Contenu du message HTML
$message = '
<html>
<head>
    <title>Confirmation d\'inscription</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Merci pour votre inscription au tournoi de pelote !</h2>
    <p>Nous sommes ravis de vous compter parmi les participants. Voici les détails de votre inscription :</p>
    <ul>
        <li><strong>Date :</strong> [Date du tournoi]</li>
        <li><strong>Lieu :</strong> [Lieu du tournoi]</li>
    </ul>
    <p>Vous trouverez ci-dessous votre confirmation d\'inscription :</p>
    <img src="https://www.exemple.com/images/confirmation.png" alt="Confirmation" style="width: 100%; max-width: 600px;">
    <p>Si vous avez des questions, n\'hésitez pas à nous contacter.</p>
    <p>À bientôt sur le terrain !</p>
    <p>Cordialement,<br> L\'équipe d\'organisation du tournoi de pelote</p>
</body>
</html>
';

// Envoi de l'email
if(mail($to, $subject, $message, $headers)) {
    echo "Email de confirmation envoyé avec succès.";
} else {
    echo "Erreur lors de l'envoi de l'email.";
}
?>
