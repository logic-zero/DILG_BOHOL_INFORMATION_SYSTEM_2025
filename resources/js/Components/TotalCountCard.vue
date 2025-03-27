<script setup>
const props = defineProps({
  title: String,
  count: [Number, String],
  icon: String,
  color: {
    type: String,
    default: 'indigo'
  }
});

const colors = {
  indigo: {
    bg: 'bg-gradient-to-br from-indigo-50 to-white',
    border: 'border-indigo-200',
    text: 'text-indigo-600'
  },
  green: {
    bg: 'bg-gradient-to-br from-green-50 to-white',
    border: 'border-green-200',
    text: 'text-green-600'
  },
  yellow: {
    bg: 'bg-gradient-to-br from-yellow-50 to-white',
    border: 'border-yellow-200',
    text: 'text-yellow-600'
  },
  fuchsia: {
    bg: 'bg-gradient-to-br from-fuchsia-50 to-white',
    border: 'border-fuchsia-200',
    text: 'text-fuchsia-600'
  },
  pink: {
    bg: 'bg-gradient-to-br from-pink-50 to-white',
    border: 'border-pink-200',
    text: 'text-pink-600'
  },
  violet: {
    bg: 'bg-gradient-to-br from-violet-50 to-white',
    border: 'border-violet-200',
    text: 'text-violet-600'
  },
  red: {
    bg: 'bg-gradient-to-br from-red-50 to-white',
    border: 'border-red-200',
    text: 'text-red-600'
  },
  orange: {
    bg: 'bg-gradient-to-br from-orange-50 to-white',
    border: 'border-orange-200',
    text: 'text-orange-600'
  },
  amber: {
    bg: 'bg-gradient-to-br from-amber-50 to-white',
    border: 'border-amber-200',
    text: 'text-amber-600'
  },
  lime: {
    bg: 'bg-gradient-to-br from-lime-50 to-white',
    border: 'border-lime-200',
    text: 'text-lime-600'
  },
  emerald: {
    bg: 'bg-gradient-to-br from-emerald-50 to-white',
    border: 'border-emerald-200',
    text: 'text-emerald-600'
  },
  teal: {
    bg: 'bg-gradient-to-br from-teal-50 to-white',
    border: 'border-teal-200',
    text: 'text-teal-600'
  },
  cyan: {
    bg: 'bg-gradient-to-br from-cyan-50 to-white',
    border: 'border-cyan-200',
    text: 'text-cyan-600'
  },
  violet: {
    bg: 'bg-gradient-to-br from-violet-50 to-white',
    border: 'border-violet-200',
    text: 'text-violet-600'
  },
  purple: {
    bg: 'bg-gradient-to-br from-purple-50 to-white',
    border: 'border-purple-200',
    text: 'text-purple-600'
  }
};
</script>

<template>
  <div class="group relative mb-4 transition-all duration-300 hover:scale-105">
    <div
      class="p-6 rounded-lg border shadow-sm hover:shadow-md"
      :class="[colors[color].bg, colors[color].border]"
    >
      <div class="flex justify-between items-start">
        <div>
          <p class="text-sm font-medium text-gray-500 mb-1">{{ title }}</p>
          <p class="text-5xl font-bold" :class="colors[color].text">
            {{ count }}
          </p>
        </div>
        <div
          v-if="icon"
          class="p-3 rounded-full"
          :class="colors[color].bg.replace('bg-gradient-to-br', 'bg')"
        >
          <i :class="`${icon} text-lg ${colors[color].text}`"></i>
        </div>
      </div>
    </div>
  </div>
</template>
