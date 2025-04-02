<script setup>
defineProps({
    title: String,
    count: Number,
    icon: String,
    bgColor: String,
    borderColor: String,
    textColor: String,
});
</script>

<template>
    <div class="group relative transition-all duration-300 hover:scale-105 w-full h-[100px]">
        <div class="h-full p-3 rounded-lg border shadow-sm hover:shadow-md flex flex-col"
             :class="[bgColor, borderColor]">
            <div class="flex justify-between items-start h-full">
                <div class="flex flex-col justify-between h-full">
                    <p class="text-xs uppercase font-medium text-gray-500">{{ title }}</p>
                    <p class="text-3xl font-bold" :class="textColor">
                        {{ count }}
                    </p>
                </div>
                <div class="p-2 rounded-full flex-shrink-0" :class="bgColor.replace('bg-', 'bg-').replace('-50', '-100')">
                    <i :class="`${icon} text-base ${textColor}`"></i>
                </div>
            </div>
        </div>
    </div>
</template>
