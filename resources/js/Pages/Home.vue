<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import Login from "../Components/Login.vue";
import SignUp from "../Components/SignUp.vue";
import InputText from "../Components/InputText.vue";
import Button from "../Components/Button.vue";
import Pusher from "pusher-js";
import Notifications from "../Components/Notifications.vue";

const user = ref();
const token = ref("");

// const user = ref({
//     id: 1,
//     name: "Pablo Crotti",
//     email: "pablo.crotti@company.ch",
// });
// const token = ref("8|PN59O7WvMnZbNiSCOtrvoewyVMpqeixbMx6BKkB758a4afbe");

const form = ref(0);

const newTitle = ref("");
const newDsc = ref("");

const newTodo = ref(false);

const todos = ref([]);

const loadData = async () => {
    if (token.value) {
        try {
            const response = await fetch("api/todos", {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token.value}`,
                },
            });
            const data = await response.json();

            todos.value = data.data;
        } catch (err) {
            console.error("Error fetching user data:", err);
        }
    }
};

const add = async () => {
    if (token.value) {
        try {
            const payload = {
                title: newTitle.value,
                description: newDsc.value,
                user_id: user.value.id,
            };
            const response = await fetch("api/todos", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${token.value}`,
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            newTitle.value = "";
            newDsc.value = "";
        } catch (err) {
            console.error("Error fetching user data:", err);
        }
    }
};

const update = async (todo) => {
    if (token.value) {
        try {
            const payload = {
                is_completed: !todo.isCompleted,
            };
            const response = await fetch(`api/todos/${todo.id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    Authorization: `Bearer ${token.value}`,
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();

            loadData();
        } catch (err) {
            console.error("Error fetching user data:", err);
        }
    }
};

const setData = (data) => {
    token.value = data.token;
    user.value = data.user;

    loadData();
};

let pusher, channel;

onMounted(() => {
    loadData();

    Pusher.logToConsole = true;

    pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: "eu",
    });

    channel = pusher.subscribe("todos");
    channel.bind("todo.updated", (data) => {
        loadData();
        newTodo.value = true;

        setTimeout(() => {
            newTodo.value = false;
        }, 5000);
    });
});

onBeforeUnmount(() => {
    if (channel) {
        channel.unbind_all();
        pusher.unsubscribe("todos");
    }
});
</script>

<template>
    <div
        class="w-screen h-screen flex justify-center items-center"
        v-if="!user"
    >
        <Login
            @set="(data) => setData(data)"
            v-if="form == 0"
            @navigate="form = 1"
        />
        <SignUp v-if="form == 1" @navigate="form = 0" />
    </div>

    <div class="w-screen h-screen flex justify-center items-center" v-else>
        <div
            :class="`fixed top-4 right-4 bg-green-200 text-green-800 transition-all duration-300 py-4 px-8 rounded-md ${
                newTodo ? 'opacity-100' : 'opacity-0'
            }`"
        >
            New todo added !
        </div>
        <div class="fixed top-4 left-4">
            <Notifications :id="`${user.id}`" :token="token" />
        </div>
        <div class="flex flex-col gap-4 w-full px-8 py-50">
            <div
                class="rounded-md bg-neutral-100 p-4"
                v-for="todo in todos"
                :key="todo.id"
            >
                <div class="flex justify-between items-center gap-4">
                    <div>
                        <h2 class="text-lg text-purple-500">
                            {{ todo.title }}
                        </h2>
                        <p class="text-neutral-500">{{ todo.description }}</p>
                    </div>
                    <button
                        @click="update(todo)"
                        :class="`w-4 h-4 rounded-sm border border-purple-500 ${
                            todo.isCompleted ? 'bg-purple-500' : ''
                        }`"
                    ></button>
                </div>
                <p class="text-sm mt-4 text-neutral-400">
                    {{ todo.user.name }}
                </p>
            </div>
        </div>
        <div class="fixed bottom-0 p-4 w-full">
            <div class="flex gap-2 w-full p-4 bg-neutral-100 rounded-md">
                <div class="max-w-[400px]">
                    <InputText
                        id="new-todo"
                        type="text"
                        label="Title"
                        placeholder="What do you want to do today ?"
                        @change="(e) => (newTitle = e.target.value)"
                    />
                </div>
                <InputText
                    id="new-todo-d"
                    type="text"
                    label="Description"
                    placeholder="Describe your task"
                    @change="(e) => (newDsc = e.target.value)"
                />
                <div class="max-w-20 flex flex-col justify-end">
                    <Button label="Add" @click="add" />
                </div>
            </div>
        </div>
    </div>
</template>
