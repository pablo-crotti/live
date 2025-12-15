<script setup>
import { ref } from "vue";
import { onMounted } from "vue";
import * as PushNotifications from "@pusher/push-notifications-web";
import { TokenProvider } from "@pusher/push-notifications-web";

const props = defineProps({ id: String, token: String });
const isSubscribed = ref(false);

// ✅ DÉFINITION GLOBALE : L'instance du client Beams est définie une seule fois
// pour être accessible par toutes les fonctions (enable, disable, check).
const beamsClient = new PushNotifications.Client({
    instanceId: import.meta.env.VITE_PUSHER_INSTANCE_ID,
});

/**
 * 🚀 Active les notifications : demande de permission, enregistrement du SW,
 * et abonnements (intérêt 'all' + setUserId pour les notifications individuelles).
 */
const enableNotifications = async () => {
    // 1. Demande de permission
    const permission = await Notification.requestPermission();
    if (permission !== "granted") return alert("Permission refusée");

    // 2. Enregistrement du Service Worker (nécessaire pour la persistance)
    navigator.serviceWorker.register("/service-worker.js").then(() => {
        console.log("Service Worker registered!");
    });

    try {
        await beamsClient.start();

        // 3. Abonnement à l'Intérêt global "all"
        await beamsClient.addDeviceInterest("all");

        // 4. Lier l'appareil à l'ID Utilisateur (CORRIGÉ)
        const tokenProvider = new TokenProvider({
            url: `/api/beams-auth`,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${props.token}`,
            },
        });

        await beamsClient.setUserId(`user-${props.id}`, tokenProvider);

        isSubscribed.value = true;
        alert("Notifications activées !");
    } catch (error) {
        console.error(
            "Erreur lors de l'activation des notifications Beams:",
            error
        );
        alert("Erreur lors de l'activation. Vérifiez la console.");
    }
};

/**
 * 🛑 Désactive les notifications : utilise clearAllState pour supprimer
 * toutes les liaisons (intérêts et ID utilisateur) pour cet appareil.
 */
const disableNotifications = async () => {
    try {
        // Supprime l'abonnement à tous les intérêts et retire la liaison à l'ID utilisateur.
        await beamsClient.clearAllState();

        isSubscribed.value = false;
        alert("Notifications désactivées !");
    } catch (error) {
        console.error(
            "Erreur lors de la désactivation des notifications Beams:",
            error
        );
        alert("Une erreur s'est produite lors de la désactivation.");
    }
};

/**
 * 🔎 Vérifie si l'appareil est déjà lié à un utilisateur ou à un intérêt.
 */
const checkSubscriptionStatus = async () => {
    try {
        // Doit être démarré pour vérifier l'état
        await beamsClient.start();

        const interests = await beamsClient.getDeviceInterests();
        const userId = await beamsClient.getUserId();

        // Si l'appareil est lié à un ID utilisateur OU abonné à un intérêt, il est considéré comme "actif"
        isSubscribed.value = userId !== null || interests.length > 0;
    } catch (e) {
        // Si Beams n'est pas encore initialisé ou s'il y a une erreur, l'état est non abonné
        isSubscribed.value = false;
        console.warn(
            "Impossible de vérifier l'état d'abonnement Beams (normal au premier chargement):",
            e
        );
    }
};

// Vérifier l'état au montage du composant
onMounted(() => {
    checkSubscriptionStatus();
});
</script>

<template>
    <div>
        <button
            @click="
                isSubscribed ? disableNotifications() : enableNotifications()
            "
            :class="{
                // Styles Tailwind CSS pour la mise en forme et le hover
                'p-2 font-semibold rounded hover:opacity-90 transition': true,
                // Styles conditionnels : Rouge si abonné, Violet si non abonné
                'bg-red-600 text-white': isSubscribed,
                'bg-purple-500 text-white': !isSubscribed,
            }"
        >
            {{
                isSubscribed
                    ? "Désactiver les notifications"
                    : "Activer les notifications"
            }}
        </button>
    </div>
</template>
