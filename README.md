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

### Push Notifications (Pusher Beams)

#### Create a Beams instance

-   Go to the Pusher dashboard
-   Create a new **Beams** instance
-   Select the appropriate app type (Web)

---

#### Service Worker

Create a `service-worker.js` file at the root of your project and add the following code:

```js
importScripts("https://js.pusher.com/beams/service-worker.js");
```

> **_NOTE:_** Because this project uses Inertia, the file was created in: `public/service-worker.js`

#### Install Pusher Beams SDK
```bash
npm i @pusher/push-notifications-web
```
---
#### Test push notifications

Add the following code in `src/main.js`

```js
const beamsClient = new PusherPushNotifications.Client({
    instanceId: 'YOUR_INSTANCE_ID',
});

beamsClient.start()
    .then(() => beamsClient.addDeviceInterest('hello'))
    .then(() => console.log('Successfully registered and subscribed!'))
    .catch(console.error);

```

Then, follow the instructions provided by Pusher to send a test notification from the dashboard.

⚠️ You may need to manually allow notifications in your browser.

---
### WebSockets (Pusher Channels)
#### Create a Pusher Channels app
- Go to the Pusher dashboard
- Create a new Channels app
- Select the frontend and backend environments
---
#### Install Pusher JS
```bash
npm install pusher-js
```
---
## Back-end Installation
### Install Laravel API
```bash
php artisan install:api
```
---
### Enable Broadcasting
```bash
php artisan install:broadcasting
```
Select the Pusher driver and provide:
-Pusher App ID
-App Key
-App Secret

*🔒 The key and secret are hidden when typing.*

### Environment configuration
Add the following variables to your `.env` file:
```
PUSHER_INSTANCE_ID=
PUSHER_PRIMARY_KEY=
```
Then, add them to `config/broadcasting.php`:
```php
'instance_id' => env('PUSHER_INSTANCE_ID'),
'secret_key' => env('PUSHER_PRIMARY_KEY'),
```
---
### Install Pusher PHP SDKs
```bash
composer require pusher/pusher-php-server
```
```bash
composer require pusher/pusher-push-notifications
```
---
## Integration
### WebSockets (Real-time events)
#### Create a Laravel Event
In this example, a `TodoUpdates` event is created:
```bash
php artisan make:event TodoUpdates
```
---
#### Broadcasting configuration
You can choose between:
- Public channel
```php
new Channel('channel-name')
```
- Private channel
```php
new PrivateChannel('channel-name')
```
---
#### Custom event name
```php
public function broadcastAs()
{
    return 'event.name';
}
```
---
#### Data sent to the frontend
```php
public function broadcastWith()
{
    return [
        'id' => $this->todo->id,
        'title' => $this->todo->title,
        'description' => $this->todo->description,
        'is_completed' => $this->todo->is_completed,
    ];
}
```
---
#### Frontend subscription
```js
onMounted(() => {

  Pusher.logToConsole = true;

  pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: "eu",
  });

  channel = pusher.subscribe("channel-name");
  channel.bind("event.name", (data) => {
    loadData();
    newTodo.value = true;

    setTimeout(() => {
      newTodo.value = false;
    }, 5000);
  });
});
```
---
#### Triggering the event
From the backend, simply dispatch the event:
```php
event(new TodoUpdates($todo));
```
---
### Push Notifications (Pusher Beams)
#### Create the service

Create the following service: `app/Services/PusherBeamsService.php` 

---
#### Send notifications to all users
```php
public function publishToAll(array $data)
{
    return $this->beamsClient->publishToInterests(
        ["all"],
        [
            "web" => [
                "notification" => [
                    "title" => $data['title'] ?? 'Notification',
                    "body"  => $data['body'] ?? '',
                ]
            ],
            "apns" => [
                "aps" => ["alert" => $data['body'] ?? '']
            ],
            "fcm" => [
                "notification" => [
                    "title" => $data['title'] ?? 'Notification',
                    "body"  => $data['body'] ?? '',
                ]
            ]
        ]
    );
}
```
---
#### Send notifications to a single user
```php
public function publishToUser(string $userId, array $data)
{
    Log::info("PusherBeams → publishToUsers()", [
        "userId" => "user-$userId",
        "payload" => [
            "title" => $data['title'] ?? null,
            "body"  => $data['body'] ?? null
        ]
    ]);

    return $this->beamsClient->publishToUsers(
        ["user-{$userId}"],
        [
            "web" => [
                "notification" => [
                    "title" => $data['title'] ?? 'Notification',
                    "body"  => $data['body'] ?? '',
                ]
            ],
            "apns" => [
                "aps" => ["alert" => $data['body'] ?? '']
            ],
            "fcm" => [
                "notification" => [
                    "title" => $data['title'] ?? 'Notification',
                    "body"  => $data['body'] ?? '',
                ]
            ]
        ]
    );
}
```
---
#### Beams Authentication (User linking)
Before handling notifications on the frontend, a secure route is required to link a user to their Beams ID.
```php
Route::middleware('auth:sanctum')->get('/beams-auth', function (Request $request) {
    $requestedBeamsId = $request->query('user_id');
    $authenticatedUserId = Auth::id();
    $expectedBeamsId = "user-{$authenticatedUserId}";

    if (!$authenticatedUserId || $requestedBeamsId !== $expectedBeamsId) {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    $beamsClient = new PushNotifications([
        'instanceId' => config('broadcasting.instance_id'),
        'secretKey'  => config('broadcasting.secret_key'),
    ]);

    return response()->json($beamsClient->generateToken($requestedBeamsId));
});
```
---
#### Frontend Notifications Component
A dedicated component was created to manage notification subscriptions: `src/components/Notifications.vue`
This component links the authenticated user to their private Pusher Beams channel and subscribes to both:
- A global channel
- A user-specific channel
```js
await beamsClient.addDeviceInterest("all");

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
```
