const webpush = require('web-push');

const vapidKeys = {
    publicKey: 'BIfEQBuK4Il1Mx1m2_dINkzOk3leS8dT07lh2oa686e_2w5NKuorz-lnZvbSdlDp234S8FrM7bH-QE7oD8i0oHk',
    privateKey: 'PSEDT8o3lrmnCNUUZitm_IKBIEN3qvFWrJEPy7iBwJk'
};

webpush.setVapidDetails(
    'mailto:admin@tournoi-pelote.com',
    vapidKeys.publicKey,
    vapidKeys.privateKey
);

const subscription = {
    endpoint: 'https://...',
    keys: {
        p256dh: '...',
        auth: '...'
    }
};

webpush.sendNotification(subscription, JSON.stringify({
    title: 'Nouvelle Notification',
    body: 'Ceci est le corps de la notification'
})).catch(error => {
    console.error('Erreur lors de l\'envoi de la notification:', error);
});
