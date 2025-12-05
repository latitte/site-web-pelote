// server.js

const express = require('express');
const bodyParser = require('body-parser');
const webpush = require('web-push');

const app = express();
app.use(bodyParser.json());

// Remplacez par vos propres clés VAPID
const vapidKeys = {
    publicKey: 'BIfEQBuK4Il1Mx1m2_dINkzOk3leS8dT07lh2oa686e_2w5NKuorz-lnZvbSdlDp234S8FrM7bH-QE7oD8i0oHk', // Remplacez par votre clé publique
    privateKey: 'PSEDT8o3lrmnCNUUZitm_IKBIEN3qvFWrJEPy7iBwJk' // Remplacez par votre clé privée
};

webpush.setVapidDetails(
    'mailto:example@yourdomain.org', // Remplacez par votre adresse e-mail
    vapidKeys.publicKey,
    vapidKeys.privateKey
);

const subscriptions = []; // Stocke les abonnements en mémoire (à remplacer par une base de données)

// Endpoint pour sauvegarder les abonnements
app.post('/save-subscription', (req, res) => {
    const subscription = req.body;
    subscriptions.push(subscription);
    res.status(201).json({ message: 'Abonnement sauvegardé' });
});

// Endpoint pour envoyer des notifications
app.post('/send-notification', (req, res) => {
    const payload = JSON.stringify(req.body.data); // Les données de notification

    const sendNotificationPromises = subscriptions.map(subscription => {
        return webpush.sendNotification(subscription, payload)
            .catch(err => console.error('Erreur lors de l\'envoi de la notification', err));
    });

    Promise.all(sendNotificationPromises).then(() => {
        res.status(200).json({ message: 'Notifications envoyées' });
    }).catch(err => {
        console.error('Erreur lors de l\'envoi des notifications', err);
        res.sendStatus(500);
    });
});

// Démarrer le serveur
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Serveur démarré sur http://localhost:${PORT}`);
});
