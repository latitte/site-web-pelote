<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annonce - Chat</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f5f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            /* align-items: center; */
            height: 100vh;
        }
        .chat-container {
            width: 75vw;
            height: 75vh;
            background: white;
            display: flex;
            flex-direction: column;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .chat-header {
            background: #f2f2f7;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .date-separator {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin: 10px 0;
        }
        .message {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .message p {
            padding: 12px;
            border-radius: 18px;
            max-width: 80%;
        }
        .message.received p {
            background: #F7F5F3;
            color: #333;
            align-self: flex-start;
        }
        .message.sent {
            align-items: flex-end;
        }
        .message.sent p {
            background: #1DAA61;
            color: white;
        }
        .message-time {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
            align-self: flex-end;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">Annonces</div>
        <div class="chat-messages">
        <?php
include '../logiciel/assets/conn_bdd.php';

// Connexion
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$sql = "SELECT user, message, date FROM newsletter ORDER BY date ASC";
$result = $conn->query($sql);

$alternance = 0;
$previous_date = '';

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $user = nl2br(htmlspecialchars($row["user"])); // sécurité + sauts de ligne
        $date = date("d F Y", strtotime($row["date"]));
        $heure = date("H:i", strtotime($row["date"]));
        $message = nl2br(htmlspecialchars($row["message"])); // sécurité + sauts de ligne

        // Si la date change, insérer un séparateur
        if ($date !== $previous_date) {
            echo "<div class='date-separator'>$date</div>";
            $previous_date = $date;
        }

        $classe = ($alternance % 2 === 0) ? 'received' : 'sent';
        echo "<div class='message $classe'>
                <p><b>$user</b><br>$message</p>
                <span class='message-time'>$heure</span>
              </div>";

        $alternance++;
    }
} else {
    echo "<p style='text-align:center; color:#999;'>Aucun message pour le moment.</p>";
}
$conn->close();
?>





        </div>
    </div>


    <script>
    window.onload = function () {
        var chatMessages = document.querySelector(".chat-messages");
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };
    </script>



</body>
</html>