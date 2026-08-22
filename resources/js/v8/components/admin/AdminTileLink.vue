<template>
	<a
		:href="href"
		:target="tile.isExternal ? '_blank' : undefined"
		class="bg-elevated hover:bg-accented rounded p-4 text-center flex flex-col items-center gap-2 cursor-pointer no-underline text-default"
		tabindex="0"
		@click="onClick"
		@keydown.enter="navigate"
		@keydown.space.prevent="navigate"
	>
		<UChip v-if="tile.num && tile.num.value > 0" :text="tile.num.value" color="primary">
			<PiMiniIcon :icon="tile.icon" class="w-6 h-6 text-2xl" fill="fill-(--ui-text)" />
		</UChip>
		<PiMiniIcon v-else :icon="tile.icon" class="w-6 h-6 text-2xl" fill="fill-(--ui-text)" />
		<span class="text-sm">{{ $t(tile.label) }}</span>
	</a>
</template>

<script lang="ts" setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import PiMiniIcon from "@/v8/components/icons/PiMiniIcon.vue";
import type { AdminTile } from "@/v8/composables/useAdminTiles";

const props = defineProps<{ tile: AdminTile }>();
const router = useRouter();

const href = computed(() => (props.tile.isExternal ? props.tile.to : router.resolve(props.tile.to).href));

function navigate() {
	if (props.tile.isExternal) {
		window.open(props.tile.to, "_blank");
	} else {
		router.push(props.tile.to);
	}
}

function onClick(event: MouseEvent) {
	// Let the browser handle external links, and modified/middle clicks on internal ones (open in new tab, etc.).
	if (props.tile.isExternal || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
		return;
	}
	event.preventDefault();
	router.push(props.tile.to);
}
</script>
