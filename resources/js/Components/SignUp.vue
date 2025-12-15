<script setup>
import Button from "./Button.vue";
import InputText from "./InputText.vue";
import { ref } from "vue";

const emit = defineEmits(["navigate"]);

const handleNavigate = () => {
    emit("navigate");
};

const email = ref("");
const password = ref("");
const name = ref("");

const handle = async () => {
    try {
        const payload = {
            name: name.value,
            email: email.value,
            password: password.value,
        };

        const response = await fetch("api/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });
        console.log(response);
        if (response.status == 201) {
            emit("navigate");
        }
    } catch (err) {
        console.error("Error during sign-up :", err);
    }
};
</script>

<template>
    <div class="w-full max-w-md bg-neutral-100 rounded-md p-8">
        <div class="flex w-full gap-8 flex-col items-center pt-4">
            <h1 class="text-4xl font-bold text-purple-500 text-left w-full">
                Sign-up
            </h1>

            <InputText
                id="name"
                type="name"
                label="Full Name"
                placeholder="John Doe"
                @change="(e) => (name = e.target.value)"
            />

            <InputText
                id="email"
                type="email"
                label="Email"
                placeholder="name@company.ch"
                @change="(e) => (email = e.target.value)"
            />
            <InputText
                id="password"
                type="password"
                label="Password"
                placeholder="Enter your password"
                @change="(e) => (password = e.target.value)"
            />

            <Button @click="handle" label="Submit" />

            <p>
                Already have an account ?
                <button
                    @click="handleNavigate"
                    class="text-purple-500 underline cursor-pointer hover:text-purple-700"
                >
                    Sign-in
                </button>
            </p>
        </div>
    </div>
</template>
