// app.js

const vapidKeys = {
    publicKey: 'BIfEQBuK4Il1Mx1m2_dINkzOk3leS8dT07lh2oa686e_2w5NKuorz-lnZvbSdlDp234S8FrM7bH-QE7oD8i0oHk', // Remplacez par votre clé publique
    privateKey: 'PSEDT8o3lrmnCNUUZitm_IKBIEN3qvFWrJEPy7iBwJk' // Remplacez par votre clé privée
};

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/service-worker.js')
            .then(function(registration) {
                console.log('Service Worker enregistré avec succès:', registration.scope);
                askNotificationPermission().then(subscription => {
                    if (subscription) {
                        sendSubscriptionToServer(subscription);
                    }
                });
            })
            .catch(function(err) {
                console.error('Échec de l\'enregistrement du Service Worker:', err);
            });
    });
}

function askNotificationPermission() {
    return Notification.requestPermission().then(function(permission) {
        if (permission === 'granted') {
            console.log('Permission de notification accordée.');
            return subscribeUserToPush();
        } else {
            console.log('Permission de notification refusée.');
            return null;
        }
    });
}

function subscribeUserToPush() {
    return navigator.serviceWorker.ready.then(function(registration) {
        const options = {
            userVisibleOnly: true,
            applicationServerKey: urlB64ToUint8Array(vapidKeys.publicKey)
        };
        return registration.pushManager.subscribe(options);
    });
}

function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    return new Uint8Array([...rawData].map(char => char.charCodeAt(0)));
}

function sendSubscriptionToServer(subscription) {
    fetch('/save-subscription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(subscription)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors de la sauvegarde de l\'abonnement');
        }
        return response.json();
    })
    .then(data => {
        console.log('Abonnement sauvegardé avec succès:', data);
    })
    .catch(error => {
        console.error('Erreur lors de la sauvegarde de l\'abonnement:', error);
    });
}

// Événement pour envoyer une notification
document.getElementById('send-notification').addEventListener('click', function() {
    sendPushNotification();
});

function sendPushNotification() {
    const data = {
        title: 'Nouvelle notification',
        body: 'Voici le contenu de la notification.'
    };

    fetch('/send-notification', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ data })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors de l\'envoi de la notification');
        }
        return response.json();
    })
    .then(data => {
        console.log('Notification envoyée avec succès:', data);
    })
    .catch(error => {
        console.error('Erreur lors de l\'envoi de la notification:', error);
    });
}
