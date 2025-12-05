<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Status du site</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            background-color: #f5f5f7;
            color: #1d1d1f;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .card {
            background-color: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid #eee;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .status-ok {
            color: green;
            font-weight: 600;
        }
        .status-down {
            color: red;
            font-weight: 600;
        }
        .changelog h3 {
            margin-bottom: 0.5rem;
        }
        .changelog ul {
            list-style-type: disc;
            margin-left: 1.5rem;
        }
        .tag {
            display: inline-block;
            background: #0071e3;
            color: white;
            padding: 0.2rem 0.6rem;
            font-size: 0.8rem;
            border-radius: 8px;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🟢 État du site</h1>

        <div class="card">
            <div class="status-item">
                <span>Connexion BDD</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>API Joueurs</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Accueil</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Newsletter</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Les équipes</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Calendrier Liste</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Calendrier Tableau</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Classement Tableau</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Classement Arbre</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Inscription</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Langue : Français</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Langue : Basque</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Accueil</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Newsletter admin</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>


            <div class="status-item">
                <span>API : Changement Niveau (phase finale)</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>API : Ajout Partie</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>API : Elimine team</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Outils de vérifications erreur</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>BackUp</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Compte Joueur</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Modification des parties par le joueur</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Page Admin</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>

            <div class="status-item">
                <span>Arbitre authentification</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Arbitre Accueil</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Arbitre Score</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Arbitre Calendrier</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Arbitre Arbitrage</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Arbitre Paiement</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>

            <div class="status-item">
                <span>Message Automatique Joueur(sms)</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
            <div class="status-item">
                <span>Message Automatique Arbitre(sms)</span>
                <span class="status-ok">✅ Fonctionnel <!--⛔ En cours de développement--></span>
            </div>

            <div class="status-item">
                <span>SMS à la demande et consultation</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>

            <div class="status-item">
                <span>Domain Name</span>
                <span class="status-ok">✅ Fonctionnel</span>
            </div>
        </div>

        <div class="card changelog">
            <h2>🆕 Nouveautés & Patches</h2>


            <div>
                <h3><span class="tag">7 juin 2025</span> Nouveauté et Mise à jour</h3>
                <ul>
                    <li>✅ Mise à jour affichage pour placer les parties</li>
                    <li>✅ Dans l'espace admin : impossible de prendre la place d'une autre partie par erreur</li>
                </ul>
            </div> 


            <div>
                <h3><span class="tag">6 juin 2025</span> Nouveauté et Mise à jour</h3>
                <ul>
                    <li>✅ Mise à jour affichage de l'espace arbitre</li>
                    <li>✅ Mise à jour du style lors du remplissage des scores pour les arbitres</li>
                </ul>
            </div>  


            <div>
                <h3><span class="tag">26 mai 2025</span> Nouveauté et Mise à jour</h3>
                <ul>
                    <li>✅ Ajout des photos d'équipe</li>
                    <li>✅ Bug d'affichage du détail des parties depuis le calendrier corrigé</li>
                    <li>✅ Possibilité de suivre le score en live</li>
                </ul>
            </div>  


            <div>
                <h3><span class="tag">20 mai 2025</span> Mise à jour</h3>
                <ul>
                    <li>✅ Passage de l'heure au format francais</li>
                    <li>✅ Possibilité de consulter ou modifier la partie depuis l'espace joueur</li>
                    <li>✅ Amélioration de la recherche des parties</li>
                </ul>
            </div>          
            
            <div>
                <h3><span class="tag">18 mai 2025</span> Nouveauté</h3>
                <ul>
                    <li>✅ Le joueur possède un compte (accès par son numéro d'équipe et son code)</li>
                    <li>✅ Le joueur peut modifier la date de ses parties depuis son espace</li>
                    <li>✅ Les deuux équipes recoivent un sms afin de confirmer leur modification</li>
                </ul>
            </div>

            <div>
                <h3><span class="tag">9 mai 2025</span> Nouveauté</h3>
                <ul>
                    <li>✅ Modification depuis la gestion du tournoi du nombre d'arbitre par soirée</li>
                    <li>✅ Envoi SMS à la demande</li>
                </ul>
            </div>



            <div>
                <h3><span class="tag">5 mai 2025</span> Nouveauté</h3>
                <ul>
                    <li>✅ Envoi SMS rappel personalisés</li>
                    <li>✅ Envoi SMS à la demande</li>
                </ul>
            </div>


            <div>
                <h3><span class="tag">2 mai 2025</span> Mise à jour</h3>
                <ul>
                    <li>✅ Ajout/gestion automatique de la newsletter</li>
                    <li>✅ Optimisation du design ordinateur/mobile</li>
                </ul>
            </div>

            <div>
                <h3><span class="tag">25 avril 2025</span> Mise à jour</h3>
                <ul>
                    <li>✅ Création de l'API pour récupérer les joueurs par ID</li>
                    <li>✅ Intégration des joueurs dynamiquement dans l'affichage des parties lors du remplissage du score</li>
                    <li>✅ Début du développement du rappel WhatsApp automatique</li>
                </ul>
            </div>

            <div>
                <h3><span class="tag">24 avril 2025</span> Patch mineur</h3>
                <ul>
                    <li>✅ Nettoyage du code de la table <code>inscriptions</code></li>
                    <li>✅ Amélioration de la détection du sous-domaine pour la base de données</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
