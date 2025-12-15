<script setup>
defineProps({
    type: { type: String, default: "text" },
    id: { type: String, default: "" },
    label: { type: String, default: "" },
    placeholder: { type: String, default: "" },
});

const emit = defineEmits(["change"]);

const handleChange = (event) => {
    emit("change", event);
};
</script>

<template>
    <div class="w-full flex flex-col-reverse gap-2">
        <input
            class="peer w-full p-4 bg-inherit border-2 rounded-md outline-none disabled:opacity-70 disabled:cursor-not-allowed border-neutral-300 focus:border-purple-500 focus:text-purple-500 transition-colors duration-300"
            :type="type"
            :placeholder="placeholder"
            :name="id"
            :id="id"
            @input="handleChange"
        />
        <label
            class="peer-focus:text-purple-500 transition-colors duration-300"
            :for="id"
            >{{ label }}</label
        >
    </div>
</template>
