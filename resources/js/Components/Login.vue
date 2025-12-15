<script setup>
import Button from "./Button.vue";
import InputText from "./InputText.vue";

const emit = defineEmits(["navigate", "set"]);

const handleNavigate = () => {
    emit("navigate");
};

const handle = async () => {
    try {
        const payload = {
            password: password.value,
            email: email.value,
        };

        const response = await fetch("api/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json();

        if (response.status == 200) {
            emit("set", {
                token: data.token,
                user: data.user,
            });
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
                Sign-in
            </h1>

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
                No account ?
                <button
                    @click="handleNavigate"
                    class="text-purple-500 underline cursor-pointer hover:text-purple-700"
                >
                    Sign-up
                </button>
            </p>
        </div>
    </div>
</template>
