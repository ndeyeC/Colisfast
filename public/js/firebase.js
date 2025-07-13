
const firebaseConfig = {
    apiKey: process.env.MIX_FIREBASE_API_KEY,
    authDomain: process.env.MIX_FIREBASE_AUTH_DOMAIN,
    projectId: process.env.MIX_FIREBASE_PROJECT_ID,
    storageBucket: process.env.MIX_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: process.env.MIX_FIREBASE_MESSAGING_SENDER_ID,
    appId: process.env.MIX_FIREBASE_APP_ID,
    measurementId: process.env.MIX_FIREBASE_MEASUREMENT_ID,
    vapidKey: process.env.MIX_FIREBASE_VAPID_KEY
};

// Initialiser Firebase
const app = firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging(app);

// Fonction pour demander la permission et obtenir le token
async function initializeFCM() {
    try {
        // Vérifier si les notifications sont supportées
        if (!('Notification' in window)) {
            console.warn('Ce navigateur ne supporte pas les notifications');
            return;
        }

        // Demander la permission
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            console.log('Permission accordée pour les notifications');
            
            // Obtenir le token FCM avec la clé VAPID depuis l'environnement
            const token = await messaging.getToken({
                vapidKey: process.env.MIX_FIREBASE_VAPID_KEY
            });
            
            if (token) {
                console.log('Token FCM récupéré:', token);
                await saveFCMToken(token);
            } else {
                console.warn('Impossible de récupérer le token FCM');
            }
        } else {
            console.warn('Permission refusée pour les notifications');
        }
    } catch (error) {
        console.error('Erreur lors de l\'initialisation FCM:', error);
    }
}

// Fonction pour sauvegarder le token FCM
async function saveFCMToken(token) {
    try {
        const response = await fetch('/user/fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ fcm_token: token })
        });

        const data = await response.json();
        
        if (data.success) {
            console.log('Token FCM sauvegardé avec succès');
            // Stocker dans une variable globale plutôt que localStorage
            window.fcmToken = token;
        } else {
            console.error('Erreur lors de la sauvegarde du token:', data.message);
        }
    } catch (error) {
        console.error('Erreur lors de la sauvegarde du token FCM:', error);
    }
}

// Écouter les messages quand l'app est au premier plan
messaging.onMessage((payload) => {
    console.log('Message reçu au premier plan:', payload);
    
    // Créer une notification personnalisée
    if (Notification.permission === 'granted') {
        const notification = new Notification(payload.notification.title, {
            body: payload.notification.body,
            icon: '/icon-192x192.png',
            requireInteraction: true
        });
        
        notification.onclick = function(event) {
            event.preventDefault();
            window.focus();
            notification.close();
        };
    }
});

// Rafraîchir le token périodiquement
messaging.onTokenRefresh(() => {
    messaging.getToken({
        vapidKey: process.env.MIX_FIREBASE_VAPID_KEY
    }).then((refreshedToken) => {
        console.log('Token rafraîchi:', refreshedToken);
        saveFCMToken(refreshedToken);
    }).catch((err) => {
        console.error('Erreur lors du rafraîchissement du token:', err);
    });
});

// Initialiser FCM quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    initializeFCM();
});

// Service Worker pour les notifications en arrière-plan
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then((registration) => {
            console.log('Service Worker enregistré:', registration);
            messaging.useServiceWorker(registration);
        })
        .catch((error) => {
            console.error('Erreur lors de l\'enregistrement du Service Worker:', error);
        });
}