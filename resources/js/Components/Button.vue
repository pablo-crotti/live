<script setup>
defineProps({
    label: { type: String, default: "" },
});

const emit = defineEmits(["click"]);

const handleClick = () => {
    emit("click");
};
</script>

<template>
    <button
        @click="handleClick"
        class="w-full p-4 border rounded-md border-purple-500 text-purple-500 hover:bg-purple-500 hover:text-white font-bold transition-colors duration-300"
    >
        {{ label }}
    </button>
</template>
