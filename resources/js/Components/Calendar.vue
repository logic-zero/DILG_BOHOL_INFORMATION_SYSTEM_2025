<script setup>
import { ref, computed, onMounted } from 'vue';

const currentDate = ref(new Date());
const holidays = ref({});
const activeHoliday = ref(null);
const tooltipStyle = ref({});

const fetchTime = async () => {
  try {
    const response = await fetch('https://worldtimeapi.org/api/timezone/Asia/Manila');
    const data = await response.json();
    currentDate.value = new Date(data.datetime);
  } catch (error) {
    console.error('Failed to fetch time:', error);
  }
};

const fetchHolidays = async () => {
  try {
    const response = await fetch('https://date.nager.at/api/v3/PublicHolidays/' + currentDate.value.getFullYear() + '/PH');
    const data = await response.json();
    holidays.value = data.reduce((acc, holiday) => {
      acc[holiday.date] = holiday.localName;
      return acc;
    }, {});
  } catch (error) {
    console.error('Failed to fetch holidays:', error);
  }
};

const getMonthName = computed(() => {
  return currentDate.value.toLocaleString('en-US', { month: 'long', year: 'numeric' });
});

const getDaysInMonth = computed(() => {
  const year = currentDate.value.getFullYear();
  const month = currentDate.value.getMonth();
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  let days = [];
  for (let i = 0; i < firstDay; i++) {
    days.push(null);
  }
  for (let i = 1; i <= daysInMonth; i++) {
    const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
    days.push({
      day: i,
      holiday: holidays.value[dateKey] || null,
      isToday: new Date().getDate() === i &&
              new Date().getMonth() === month &&
              new Date().getFullYear() === year,
      dateKey: dateKey,
      col: (firstDay + i - 1) % 7,
      row: Math.floor((firstDay + i - 1) / 7)
    });
  }
  return days;
});

const changeMonth = async (direction) => {
  currentDate.value.setMonth(currentDate.value.getMonth() + direction);
  currentDate.value = new Date(currentDate.value);
  activeHoliday.value = null;
  await fetchHolidays();
};

const showHoliday = (dateKey, col, row) => {
  activeHoliday.value = dateKey;

  const isTopHalf = row < 3;
  const isLeftEdge = col < 2;
  const isRightEdge = col > 4;

  tooltipStyle.value = {
    position: 'absolute',
    width: '140px',
    left: isLeftEdge ? '0' : isRightEdge ? 'auto' : '50%',
    right: isRightEdge ? '0' : 'auto',
    transform: isLeftEdge || isRightEdge ? 'none' : 'translateX(-50%)',
    [isTopHalf ? 'top' : 'bottom']: '100%',
    marginTop: isTopHalf ? '2px' : '0',
    marginBottom: isTopHalf ? '0' : '2px',
    zIndex: 20
  };
};

const hideHoliday = () => {
  activeHoliday.value = null;
};

onMounted(async () => {
  await fetchTime();
  await fetchHolidays();
});
</script>

<template>
  <div class="h-[300px] md:h-[30%] min-h-[300px] bg-white rounded shadow-sm p-3 flex flex-col border border-gray-100 relative">
    <div class="flex justify-between items-center mb-2 px-1">
      <button @click="changeMonth(-1)" class="p-1 rounded-full hover:bg-gray-100 transition-colors" aria-label="Previous month">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <span class="text-base font-medium text-gray-700">{{ getMonthName }}</span>
      <button @click="changeMonth(1)" class="p-1 rounded-full hover:bg-gray-100 transition-colors" aria-label="Next month">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <div class="grid grid-cols-7 gap-1 mb-1">
      <span v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']"
            :key="day"
            class="text-xs font-medium text-gray-500 text-center h-6 flex items-center justify-center">
        {{ day }}
      </span>
    </div>

    <div class="grid grid-cols-7 gap-1 flex-grow overflow-hidden">
      <template v-for="(item, index) in getDaysInMonth" :key="index">
        <div v-if="item"
             class="relative flex flex-col items-center p-0.5 group"
             :class="{
               'text-red-500': item.holiday,
               'text-gray-400': new Date(currentDate.getFullYear(), currentDate.getMonth(), item.day) < new Date(new Date().setHours(0,0,0,0))
             }"
             @mouseenter="item.holiday ? showHoliday(item.dateKey, item.col, item.row) : null"
             @mouseleave="hideHoliday"
             @click="item.holiday ? showHoliday(item.dateKey, item.col, item.row) : null">

          <span class="text-xs flex items-center justify-center w-6 h-6 rounded-full leading-6 transition-all group-hover:bg-gray-100"
                :class="{
                  'bg-blue-500 text-white shadow-[0_0_0_2px_rgba(59,130,246,0.3)] group-hover:bg-blue-600': item.isToday,
                  'font-medium': item.holiday || item.isToday
                }">
            {{ item.day }}
          </span>

          <div v-if="item.holiday && activeHoliday === item.dateKey"
               class="absolute bg-white p-2 text-xs font-medium text-red-500 rounded shadow-md border border-gray-200 whitespace-normal break-words text-center"
               :style="tooltipStyle">
            {{ item.holiday }}
          </div>
        </div>
        <div v-else class="min-h-[28px]"></div>
      </template>
    </div>
  </div>
</template>
