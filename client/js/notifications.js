if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.register('/sw.js')
    .then(swReg => {
        console.log('Service Worker enregistré', swReg);
        document.getElementById('subscribe').addEventListener('click', async () => {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                const subscription = await swReg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('<VAPID_PUBLIC_KEY>')
                });

                // Envoie au serveur
                fetch('/subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(subscription)
                });
            }
        });
    });
}

// Utilitaire
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
}
