<template>
<div :class="{
    'absolute flex flex-col text-muted-color-emphasis text-right top-0 h-full overflow-y-scroll no-scrollbar': true,
    'right-6': !isTouch,
    'right-2': isTouch,
    'pt-14': !is_full_screen,
    'bg-gradient-to-l from-(--p-surface-0) dark:from-(--p-surface-900) text-shadow-sm  group pb-48': true,
    }">
    <div v-for="yearChunk in dates" :key="yearChunk.header" class="flex flex-col">
        <span class="font-semibold text-lg">{{ yearChunk.header }}</span>
        <!-- <span :class="{
            'text-3xs text-muted-color text-right scale-50 origin-right': true,
            'inline-block group-hover:hidden': !isTouch,
            'hidden': isTouch,
            }"><i class="pi pi-circle-fill" /></span> -->
        <span
            v-for="monthChunk in yearChunk.data"
            :class="{
                'cursor-pointer text-muted-color transition-all duration-150 scale-75 ease-in-out origin-right': true,
                'hover:text-primary-500 group-hover:inline-block hover:scale-100 hidden': !isTouch
                }"
            :key="monthChunk.header"
            @click="emits('load', monthChunk.data[0])">{{ monthChunk.header }}
        </span>
    </div>
</div>
</template>
<script setup lang="ts">
import { useSplitter } from '@/composables/album/splitter';
import { useTogglablesStateStore } from '@/stores/ModalsState';
import { isTouchDevice } from '@/utils/keybindings-utils';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';
import { computed } from 'vue';

const props = defineProps<{
    dates: string[]
}>();

const { spliter } = useSplitter()

const togglableStore = useTogglablesStateStore();
const { is_full_screen } = storeToRefs(togglableStore);

const dates = computed(() => {
    const splitMonth = spliter(props.dates, (d) => d.split('-')[0] + d.split('-')[1], (d) => d.split('-')[0] + '-' + d.split('-')[1]);
    return spliter(splitMonth, (d) => d.header.split('-')[0], (d) => d.header.split('-')[0]);
})

const isTouch = ref(isTouchDevice())

const emits = defineEmits<{
    load: [date: string];
}>();

</script>