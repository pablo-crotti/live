<script setup>
import { ref, onMounted } from "vue";

const d = ref("hello");

const loadData = async () => {
    try {
        const response = await fetch("/api");
        const data = await response.json();
        d.value = data.message;
    } catch (err) {
        console.log("Error fetching data :", err);
    }
};

onMounted(() => {
    loadData();
});
</script>

<template>
    <h1 class="text-4xl font-bold text-red-500">Hello world</h1>
    <p class="text-green-500">{{ d }}</p>
</template>
