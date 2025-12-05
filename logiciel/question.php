<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <style>
        .chat-container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .question-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .question-box {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #e6f7ff;
            cursor: pointer;
        }
        .question-box:hover {
            background-color: #ccebff;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background-color: #e6f7ff;
        }
        .bot-message {
            text-align: left;
        }
        .user-message {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div id="chat-box"></div>
        <div class="question-container" id="question-container">
            <!-- Questions principales à remplir dynamiquement -->
        </div>
    </div>

    <script>
        var currentStep = 1; // Étape actuelle du processus de questions
        var userAnswers = {}; // Réponses de l'utilisateur

        // Définition des étapes et des questions
        var steps = {
            1: {
                question: 'Que souhaitez vous ?',
                options: [
                    'Renseignement sur une partie',
                    'Qui contacter ?',
                    'Créneaux disponibles'
                ]
            },
            'Renseignement sur une partie': {
                question: 'Une partie ?',
                options: [
                    'Quand est ce que je joue',
                    'Replacer une partie'
                ]
            },
            'Quand est ce que je joue': {
                question: 'Je joue',
                options: [
                    'Catalogne',
                    'Andalousie',
                    'Madrid'
                ]
            },
            Italie: {
                question: 'Dans quelle région habitez-vous en Italie ?',
                options: [
                    'Lombardie',
                    'Latium',
                    'Sicile'
                ]
            },
            'Île-de-France': {
                question: 'Quel est votre département en Île-de-France ?',
                options: [
                    'Paris',
                    'Seine-et-Marne',
                    'Yvelines'
                ]
            },
            'Provence-Alpes-Côte d\'Azur': {
                question: 'Quel est votre département en Provence-Alpes-Côte d\'Azur ?',
                options: [
                    'Bouches-du-Rhône',
                    'Var',
                    'Alpes-Maritimes'
                ]
            },
            'Occitanie': {
                question: 'Quel est votre département en Occitanie ?',
                options: [
                    'Haute-Garonne',
                    'Hérault',
                    'Gard'
                ]
            },
            // Ajouter d'autres étapes selon vos besoins
        };

        // Fonction pour initialiser les questions
        function initQuestions() {
            var questionContainer = document.getElementById('question-container');
            var currentQuestion = steps[currentStep];

            // Effacer les questions précédentes
            questionContainer.innerHTML = '';

            // Affichage de la question actuelle
            var questionElement = '<div class="question-box" onclick="selectQuestion(' + currentStep + ')">' + currentQuestion.question + '</div>';
            //questionContainer.innerHTML += questionElement;

            // Affichage des options de la question actuelle
            var optionsElement = currentQuestion.options.map(function(option) {
                // Vérifier si l'option a déjà été choisie
                if (!userAnswers[option]) {
                    return '<div class="question-box" onclick="selectOption(\'' + option + '\')">' + option + '</div>';
                } else {
                    return ''; // Ne pas afficher l'option déjà choisie
                }
            }).join('');
            questionContainer.innerHTML += optionsElement;
        }

        // Fonction pour sélectionner une question
        function selectQuestion(step) {
            currentStep = step;
            displayMessage(steps[currentStep].question, 'user');
            initQuestions();
        }

        // Fonction pour sélectionner une option
        function selectOption(option) {
            userAnswers[currentStep] = option;
            displayMessage(option, 'user');

            // Si la prochaine étape existe, afficher la question suivante
            if (typeof steps[option] !== 'undefined') {
                currentStep = option;
                displayMessage(steps[currentStep].question, 'bot');
                initQuestions();
            } else {
                displayMessage('Merci pour vos réponses.', 'bot');
                // Vous pouvez gérer ici la suite de l'interaction ou l'envoi des réponses à un serveur
            }
        }

        // Fonction pour afficher un message dans le chat
        function displayMessage(message, sender) {
            var chatBox = document.getElementById('chat-box');
            var messageClass = sender === 'user' ? 'user-message' : 'bot-message';
            var messageElement = '<div class="message ' + messageClass + '">' + message + '</div>';
            chatBox.innerHTML += messageElement;
            chatBox.scrollTop = chatBox.scrollHeight; // Fait défiler vers le bas pour voir le dernier message
        }

        // Initialiser les questions au chargement de la page
        window.onload = function() {
            initQuestions();
        };
    </script>
</body>
</html>
