<script>
const loadData = async () => {
    try {
        const response = await fetch("/api");
        const data = await response.json();

        return data.message;
    } catch (err) {
        console.log("Error fetching data :", err);
    }
};

export default {
    mounted() {
        data = ref("");

        data = loadData();
    },
};
</script>
<template>
    <h1 class="text-4xl font-bold text-red-500">Hello world</h1>
    <p class="text-green-500">{{ data }}</p>
</template>
