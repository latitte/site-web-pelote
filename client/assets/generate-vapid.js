const webpush = require('web-push');

// Générer des clés VAPID
const vapidKeys = webpush.generateVAPIDKeys();

console.log('Clé publique VAPID:', vapidKeys.publicKey);
console.log('Clé privée VAPID:', vapidKeys.privateKey);
