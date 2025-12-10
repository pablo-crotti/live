<script>
const loadData = async () => {
    try {
        const response = await fetch("localhost:8000/api");
        const data = await response.json();

        console.log(data);
    } catch (err) {
        console.log("Error fetching data :", err);
    }
};

export default {
    mounted() {
        console.log(mounted);
        loadData();
    },
};
</script>
<template>
    <h1 class="text-4xl font-bold text-red-500">Hello world</h1>
    <p class="text-green-500">Test</p>
</template>
