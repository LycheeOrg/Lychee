<template>
	<UModal v-model:open="is_album_edit_open" fullscreen :title="modalTitle" :close="{ icon: 'lucide:x' }">
		<template #body>
			<div class="w-full max-w-6xl mx-auto">
				<!-- Mobile fallback: the aside (where this same toggle lives on desktop) is hidden below `lg`. -->
				<AlbumExpertModeToggle v-if="albumStore.config?.is_base_album" v-model="is_expert_mode" class="mb-6 lg:hidden" />
				<div v-if="sections.length > 1" class="flex relative items-start justify-between gap-8">
					<UPageAside class="hidden sticky lg:block lg:top-0 w-52 shrink-0 py-0">
						<AlbumExpertModeToggle v-if="albumStore.config?.is_base_album" v-model="is_expert_mode" class="mb-6" />
						<UNavigationMenu orientation="vertical" :items="navItems" highlight :dir="isLTR() ? 'ltr' : 'rtl'" />
					</UPageAside>
					<div class="w-full min-w-0 flex flex-col gap-10">
						<section id="album-settings-about" class="w-full flex justify-center flex-wrap items-start gap-4 scroll-mt-4">
							<AlbumProperties
								v-if="albumStore.config?.is_base_album"
								:key="`properties_${albumStore.album?.id}`"
								:expert-mode="is_expert_mode"
								legend-icon="lucide:info"
								:legend-label="$t('gallery.album.tabs.about')"
							/>
						</section>
						<section id="album-settings-visibility" class="w-full flex justify-center flex-wrap gap-4 scroll-mt-4">
							<AlbumVisibility
								:key="`visibility_${albumStore.album?.id}`"
								legend-icon="lucide:eye"
								:legend-label="$t('gallery.album.tabs.visibility')"
							/>
						</section>
						<section v-if="canMove" id="album-settings-move" class="w-full flex justify-center flex-wrap gap-4 scroll-mt-4">
							<AlbumMove
								:key="`move_${albumStore.album?.id}`"
								legend-icon="lucide:folder"
								:legend-label="$t('gallery.album.tabs.move')"
							/>
						</section>
						<section v-if="canShare" id="album-settings-share" class="w-full flex justify-center flex-wrap gap-4 scroll-mt-4">
							<AlbumShare
								:key="`share_${albumStore.album?.id}`"
								legend-icon="lucide:users"
								:legend-label="$t('gallery.album.tabs.share')"
							/>
						</section>
						<section v-if="canManagePurchase" id="album-settings-shop" class="w-full flex justify-center flex-wrap gap-4 scroll-mt-4">
							<AlbumPurchasable
								:key="`purchasable_${albumStore.album?.id}`"
								legend-icon="lucide:shopping-cart"
								:legend-label="$t('gallery.album.tabs.shop')"
							/>
						</section>
						<section
							v-if="canDelete || (canTransfer && is_expert_mode)"
							id="album-settings-danger"
							class="w-full flex justify-center flex-wrap gap-4 scroll-mt-4"
						>
							<Fieldset class="w-full">
								<template #legend>
									<span class="flex items-center gap-2 text-error"
										><UIcon name="lucide:triangle-alert" />{{ $t("gallery.album.tabs.danger") }}</span
									>
								</template>
								<div class="flex flex-col gap-6">
									<AlbumTransfer v-if="canTransfer && is_expert_mode" :key="`transfer_${albumStore.album?.id}`" />
									<AlbumDelete v-if="canDelete" :key="`delete_${albumStore.album?.id}`" @deleted="close" />
								</div>
							</Fieldset>
						</section>
					</div>
				</div>
				<div v-else class="w-full flex justify-center flex-wrap gap-4">
					<AlbumVisibility
						:key="`visibility_${albumStore.album?.id}`"
						legend-icon="lucide:eye"
						:legend-label="$t('gallery.album.tabs.visibility')"
					/>
				</div>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, nextTick, onUnmounted, ref, watch } from "vue";
import { trans } from "laravel-vue-i18n";
import type { NavigationMenuItem } from "@nuxt/ui";
import UsersService from "@/services/users-service";
import AlbumExpertModeToggle from "@/v8/components/forms/album/AlbumExpertModeToggle.vue";
import AlbumProperties from "@/v8/components/forms/album/AlbumProperties.vue";
import AlbumVisibility from "@/v8/components/forms/album/AlbumVisibility.vue";
import AlbumDelete from "@/v8/components/forms/album/AlbumDelete.vue";
import AlbumMove from "@/v8/components/forms/album/AlbumMove.vue";
import AlbumPurchasable from "@/v8/components/forms/album/AlbumPurchasable.vue";
import AlbumTransfer from "@/v8/components/forms/album/AlbumTransfer.vue";
import AlbumShare from "@/v8/components/forms/album/AlbumShare.vue";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLtRorRtL } from "@/utils/Helpers";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLycheeStateStore } from "@/stores/LycheeState";

const { isLTR } = useLtRorRtL();

const albumStore = useAlbumStore();
const togglableStore = useTogglablesStateStore();
const { is_album_edit_open } = storeToRefs(togglableStore);
const { expert_album_settings } = storeToRefs(useLycheeStateStore());

const is_expert_mode = ref(expert_album_settings.value);

// Show which album is being edited: "Album Settings: My Album". Separator and word order live in
// the translation string so locales can differ; falls back to the plain section title while the
// album is still loading.
const modalTitle = computed(() => {
	const albumTitle = albumStore.album?.title;
	return albumTitle ? trans("gallery.album.edit_title_with_name", { title: albumTitle }) : trans("gallery.album.edit_title");
});

const numUsers = ref(0);

UsersService.count().then((data) => {
	numUsers.value = data.data;
});

const canShare = computed(() => albumStore.rights?.can_share_with_users && numUsers.value > 1 && albumStore.config?.is_base_album);
const canMove = computed(() => albumStore.config?.is_model_album && albumStore.rights?.can_move);
const canTransfer = computed(() => albumStore.config?.is_base_album && numUsers.value > 1 && albumStore.rights?.can_transfer);
const canDelete = computed(() => albumStore.config?.is_base_album && albumStore.rights?.can_delete);
const canManagePurchase = computed(() => albumStore.config?.is_model_album && albumStore.rights?.can_make_purchasable);

type SectionId = "about" | "visibility" | "share" | "move" | "shop" | "danger";
type Section = { value: SectionId; label: string; class?: string };

// Everything renders on one continuously-scrolling page (like the admin Settings "All Settings"
// view) instead of swapping tab panels; this list only drives the jump-to-section side menu.
const sections = computed<Section[]>(() => {
	if (!albumStore.config?.is_base_album) {
		return [];
	}

	const items: Section[] = [
		{ value: "about", label: trans("gallery.album.tabs.about") },
		{ value: "visibility", label: trans("gallery.album.tabs.visibility") },
	];
	if (canMove.value) {
		items.push({ value: "move", label: trans("gallery.album.tabs.move") });
	}
	if (canShare.value) {
		items.push({ value: "share", label: trans("gallery.album.tabs.share") });
	}
	if (canManagePurchase.value) {
		items.push({ value: "shop", label: trans("gallery.album.tabs.shop") });
	}
	if (canDelete.value || (canTransfer.value && is_expert_mode.value)) {
		items.push({ value: "danger", label: trans("gallery.album.tabs.danger"), class: "text-error" });
	}
	return items;
});

// Section currently scrolled into view, tracked via a scroll listener below.
const activeSection = ref<SectionId>("about");

const navItems = computed<NavigationMenuItem[]>(() =>
	sections.value.map((s) => ({
		label: s.label,
		class: s.class,
		active: activeSection.value === s.value,
		onSelect: (e: Event) => {
			e.preventDefault();
			goto(s.value);
		},
	})),
);

// Prefixed so these can never collide with an unrelated element elsewhere on the page sharing the
// same plain id - e.g. an icon sprite's <symbol id="move"> / <symbol id="share">, which silently
// stole document.getElementById("move")/("share") out from under the bare section ids before.
const SECTION_ID_PREFIX = "album-settings-";

function sectionElementId(id: SectionId): string {
	return `${SECTION_ID_PREFIX}${id}`;
}

function goto(id: SectionId) {
	document.getElementById(sectionElementId(id))?.scrollIntoView({ behavior: "smooth" });
}

// How close to the top of the scroll container (in px) a section's heading must be before it's
// considered "read" and takes over the highlight.
const READ_LINE_OFFSET = 96;

function findScrollContainer(el: HTMLElement): HTMLElement | null {
	let node = el.parentElement;
	while (node) {
		const overflowY = getComputedStyle(node).overflowY;
		if (overflowY === "auto" || overflowY === "scroll") {
			return node;
		}
		node = node.parentElement;
	}
	return null;
}

// Picks the last section (in DOM order) whose heading has scrolled up past the read line, rather
// than requiring the section to still occupy a slice of the viewport. An IntersectionObserver-based
// band only catches sections tall enough to linger inside it, so short ones (e.g. "Move Album",
// a single field) could scroll past without ever lighting up their nav entry.
function updateActiveSection() {
	const elements = sections.value
		.map((s) => ({ id: s.value, el: document.getElementById(sectionElementId(s.value)) }))
		.filter((entry): entry is { id: SectionId; el: HTMLElement } => entry.el !== null);
	if (elements.length === 0) {
		return;
	}

	let current = elements[0].id;
	for (const { id, el } of elements) {
		if (el.getBoundingClientRect().top <= READ_LINE_OFFSET) {
			current = id;
		} else {
			break;
		}
	}
	activeSection.value = current;
}

let scrollContainer: HTMLElement | null = null;
let scrollTicking = false;

function onScroll() {
	if (scrollTicking) {
		return;
	}
	scrollTicking = true;
	requestAnimationFrame(() => {
		updateActiveSection();
		scrollTicking = false;
	});
}

let setupRetryHandle: ReturnType<typeof setTimeout> | null = null;

function cleanupScrollTracking() {
	scrollContainer?.removeEventListener("scroll", onScroll);
	scrollContainer = null;
	if (setupRetryHandle !== null) {
		clearTimeout(setupRetryHandle);
		setupRetryHandle = null;
	}
}

// The modal mounts its content (via transition + portal) slightly after `is_album_edit_open`
// flips and a single `nextTick()` resolves, so the first attempt often finds no section elements
// yet. Poll briefly until they exist rather than silently giving up on that first miss.
function setupScrollTracking(retriesLeft = 20) {
	cleanupScrollTracking();

	const firstSection = sections.value[0];
	const first = firstSection ? document.getElementById(sectionElementId(firstSection.value)) : null;
	if (!first) {
		if (retriesLeft > 0) {
			setupRetryHandle = setTimeout(() => setupScrollTracking(retriesLeft - 1), 50);
		}
		return;
	}

	scrollContainer = findScrollContainer(first);
	if (!scrollContainer) {
		return;
	}
	scrollContainer.addEventListener("scroll", onScroll, { passive: true });
	updateActiveSection();
}

// `is_album_edit_open` can flip in either direction: externally (the header's toggle button
// mutates the store ref directly) or internally (the modal's own close button/escape/overlay
// click, which round-trips through v-model). Watching the ref itself - rather than the modal's
// `update:open` emit - is the only thing that reliably fires for both.
watch(is_album_edit_open, (open) => {
	if (open) {
		activeSection.value = "about";
		nextTick(() => setupScrollTracking());
	} else {
		cleanupScrollTracking();
	}
});

watch(sections, () => {
	if (is_album_edit_open.value) {
		nextTick(() => setupScrollTracking());
	}
});

onUnmounted(cleanupScrollTracking);

function close() {
	is_album_edit_open.value = false;
}
</script>
