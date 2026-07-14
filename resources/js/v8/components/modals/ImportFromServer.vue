<template>
	<UModal v-model:open="open" :dismissible="true">
		<template #body>
			<div v-if="importing">
				<div class="flex flex-col items-center justify-center gap-4 py-4">
					<Spinner class="text-4xl text-primary-500" />
					<p class="text-base text-muted">{{ $t("import_from_server.importing_please_be_patient") }}</p>
				</div>
			</div>
			<div class="flex flex-col" v-else-if="importState.options">
				<h2 class="text-highlighted font-bold text-lg mb-2">{{ $t("import_from_server.title") }}</h2>
				<p class="mb-5 text-base">
					{{ $t("import_from_server.description") }}
				</p>
				<div class="mb-2">
					<span class="text-muted">{{ $t("import_from_server.selected_directory") }}</span>
					<pre class="text-sm font-mono w-full overflow-clip">{{ directory }}</pre>
				</div>
				<div class="flex flex-col text-xs max-h-48 overflow-y-scroll">
					<div
						v-for="dir in directories"
						@click="onBrowse(dir)"
						:key="`${directory}/${dir}`"
						class="cursor-pointer hover:bg-primary-500/10 p-0.5 bg-elevated/25 dark:border-t-neutral-700/50 border-t first:border-none font-mono"
					>
						{{ dir }}
					</div>
				</div>
				<div class="flex flex-col gap-2 mt-4">
					<form>
						<USwitch v-model="importState.options.delete_imported" color="error" :ui="{ label: 'text-sm' }">
							<template #label>
								{{ $t("import_from_server.delete_imported") }}
								<UIcon v-if="importState.options.delete_imported" name="lucide:triangle-alert" class="text-error text-xs" />
							</template>
						</USwitch>
						<USwitch
							v-model="importState.options.import_via_symlink"
							:label="$t('import_from_server.import_via_symlink')"
							:ui="{ label: 'text-sm' }"
						/>
						<USwitch
							v-model="importState.options.skip_duplicates"
							:label="$t('import_from_server.skip_duplicates')"
							:ui="{ label: 'text-sm' }"
						/>
						<USwitch
							v-model="importState.options.resync_metadata"
							:label="$t('import_from_server.resync_metadata')"
							:ui="{ label: 'text-sm' }"
						/>
						<USwitch v-model="importState.options.delete_missing_photos" color="warning" :ui="{ label: 'text-sm' }">
							<template #label>
								{{ $t("import_from_server.delete_missing_photos") }}
								<UIcon v-if="importState.options.delete_missing_photos" name="lucide:triangle-alert" class="text-warning text-xs" />
							</template>
						</USwitch>
						<USwitch v-model="importState.options.delete_missing_albums" color="warning" :ui="{ label: 'text-sm' }">
							<template #label>
								{{ $t("import_from_server.delete_missing_albums") }}
								<UIcon v-if="importState.options.delete_missing_albums" name="lucide:triangle-alert" class="text-warning text-xs" />
							</template>
						</USwitch>
					</form>
				</div>
			</div>
			<div v-else class="py-4">
				{{ $t("import_from_server.loading") }}
			</div>
		</template>
		<template #footer v-if="!importing && importState.options">
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="closeCallback">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="submit">
					{{ $t("import_from_server.sync") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { onMounted, ref, watch } from "vue";
import ImportService, { ImportFromServerRequest } from "@/services/import-service";
import AlbumService from "@/services/album-service";
import Spinner from "@/v8/components/Spinner.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useImportState } from "@/stores/ImportState";

const open = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{ refresh: [] }>();
const toast = useAppToast();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);

const importState = useImportState();
const directory = ref<string>("");
const importing = ref<boolean>(false);

const directories = ref<string[]>([]);

// We saved previously browsed directories to avoid reloading them
const browsed = ref<Map<string, string[]>>(new Map<string, string[]>());

function browse(dir: string) {
	if (browsed.value.has(dir)) {
		// We have validated that the key exists, so the cast as is safe
		directories.value = browsed.value.get(dir) as string[];
		console.log("Using cached browse for", dir, directories.value);
		return;
	}

	ImportService.browse(dir).then((response) => {
		directories.value = response.data;
		browsed.value.set(dir, response.data);
	});
}

function onBrowse(dir: string) {
	// Clear current directories immediately to avoid confusion
	if (dir === "..") {
		const parts = directory.value.split("/");
		parts.pop();
		directory.value = parts.join("/");
		directory.value = normalize(directory.value);
		console.log("cd .. to", directory.value);
	} else {
		directory.value = normalize(directory.value);
		directory.value = directory.value + "/" + dir;
	}
	browse(directory.value);
}

function normalize(path: string): string {
	if (path === "") {
		return "/";
	}
	if (path === "/") {
		return "";
	}
	return path;
}

function submit() {
	if (!importState.options) {
		return;
	}

	const payload: ImportFromServerRequest = {
		album_id: getParentId() ?? null,
		directories: [directory.value],
		delete_imported: importState.options.delete_imported,
		import_via_symlink: importState.options.import_via_symlink,
		skip_duplicates: importState.options.skip_duplicates,
		resync_metadata: importState.options.resync_metadata,
		delete_missing_photos: importState.options.delete_missing_photos,
		delete_missing_albums: importState.options.delete_missing_albums,
	};

	importing.value = true;
	ImportService.importFromServer(payload).then(() => {
		directory.value = "";
		open.value = false;
		toast.add({ severity: "success", summary: "Success", detail: "Import started successfully", life: 3000 });
		// Clear cache for the parent album to ensure the new photos are displayed
		AlbumService.clearCache();
		importing.value = false;
		emits("refresh");
	});
}

function closeCallback() {
	open.value = false;
}

onMounted(() => {
	importState.load();
});

watch(
	() => open.value,
	(newVal) => {
		if (newVal) {
			importState.load().then(() => {
				// reset state
				directory.value = importState.directory;
				browse(directory.value);
			});
		}
	},
);
</script>
