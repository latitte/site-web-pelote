<?php
// Fonction pour obtenir les sous-questions en fonction de l'étape et de l'option sélectionnée
function getSubQuestions($step, $option) {
    switch ($step) {
        case 1:
            switch ($option) {
                case 'France':
                    return array(
                        'Quelle est votre région en France ?',
                        'Quelle est votre ville en France ?'
                    );
                case 'Espagne':
                    return array(
                        'Quelle est votre région en Espagne ?',
                        'Quelle est votre ville en Espagne ?'
                    );
                case 'Italie':
                    return array(
                        'Quelle est votre région en Italie ?',
                        'Quelle est votre ville en Italie ?'
                    );
                default:
                    return array();
            }
        case 2:
            switch ($option) {
                case 'Ile-de-France':
                    return array(
                        'Quelle est votre département en Île-de-France ?',
                        'Quelle est votre commune en Île-de-France ?'
                    );
                case 'Provence-Alpes-Côte d\'Azur':
                    return array(
                        'Quelle est votre département en Provence-Alpes-Côte d\'Azur ?',
                        'Quelle est votre commune en Provence-Alpes-Côte d\'Azur ?'
                    );
                case 'Lombardie':
                    return array(
                        'Quelle est votre province en Lombardie ?',
                        'Quelle est votre commune en Lombardie ?'
                    );
                default:
                    return array();
            }
        case 3:
            // Ajoutez des sous-questions en fonction de l'option pour l'étape 3
            return array();
        // Ajoutez d'autres cas selon vos besoins
        default:
            return array();
    }
}

// Vérifie si une option a été sélectionnée via POST
if (isset($_POST['option'])) {
    $selectedStep = intval($_POST['step']);
    $selectedOption = $_POST['option'];
    
    // Renvoie les sous-questions appropriées
    $subQuestions = getSubQuestions($selectedStep, $selectedOption);
    
    // Prépare la réponse JSON pour envoyer à l'interface utilisateur
    $response = array(
        'question' => 'Voici une réponse à votre choix : ' . $selectedOption,
        'subQuestions' => $subQuestions
    );
    
    echo json_encode($response);
} else {
    echo 'Veuillez sélectionner une option.';
}
?>
