// service-worker.js

self.addEventListener('push', function(event) {
  const data = event.data ? event.data.json() : {};
  const options = {
      body: data.body || 'Vous avez une nouvelle notification!',
      icon: '/images/icon-192x192.png', // Remplacez par votre icône
      badge: '/images/badge.png', // Remplacez par votre badge
      actions: [
          { action: 'open_url', title: 'Ouvrir' }
      ]
  };
  event.waitUntil(
      self.registration.showNotification(data.title || 'Notification', options)
  );
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  event.waitUntil(
      clients.openWindow('https://ilharre.tournoi-pelote.com') // Remplacez par votre URL
  );
});
