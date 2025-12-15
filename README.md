# Pusher Notifications with Laravel & Vue (Inertia)

This project demonstrates how to implement **real-time events (Pusher Channels)** and **push notifications (Pusher Beams)** using:

-   **Laravel** as the backend (API)
-   **Vue.js** as the frontend
-   **Inertia.js** for rendering Vue pages inside Laravel
-   **Pusher** for WebSockets and push notifications

A **Pusher account** is required:  
👉 https://pusher.com/

---

## Front-end Installation

After installing Vue in your Laravel + Inertia project, follow the steps below.

---

## Push Notifications (Pusher Beams)

### Create a Beams instance

-   Go to the Pusher dashboard
-   Create a new **Beams** instance
-   Select the appropriate app type (Web)

---

### Service Worker

Create a `service-worker.js` file at the root of your project and add the following code:

```js
importScripts("https://js.pusher.com/beams/service-worker.js");
```

> **_NOTE:_** Because this project uses Inertia, the file was created in: `public/service-worker.js`
