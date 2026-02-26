<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none w-[36rem] max-w-[95vw]" modal :dismissable-mask="true">
		<template #container>
			<!-- Step 1: Rule Selection -->
			<div v-if="step === 'select'" class="p-9 text-center text-muted-color">
				<p class="text-sm/8 font-bold mb-4">{{ $t("dialogs.apply_renamer.title") }}</p>
				<p class="text-sm/8 mb-4">{{ $t("dialogs.apply_renamer.description") }}</p>

				<div v-if="is_loading_rules" class="flex justify-center py-4">
					<i class="pi pi-spin pi-spinner text-2xl"></i>
				</div>
				<div v-else-if="rules.length === 0" class="py-4 text-sm text-muted-color">
					{{ $t("dialogs.apply_renamer.no_rules") }}
				</div>
				<div v-else class="text-left space-y-2 max-h-60 overflow-y-auto">
					<div
						v-for="rule in rules"
						:key="rule.id"
						class="flex items-start gap-2 p-2 rounded hover:bg-surface-100 dark:hover:bg-surface-800"
					>
						<Checkbox v-model="selected_rule_ids" :value="rule.id" :input-id="'rule-' + rule.id" class="mt-0.5" />
						<label :for="'rule-' + rule.id" class="cursor-pointer flex-1">
							<div class="font-semibold text-sm">{{ rule.rule }}</div>
							<div class="text-xs text-muted-color mt-1 space-y-0.5">
								<div class="flex gap-2">
									<span class="font-mono bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">{{ rule.mode }}</span>
									<span v-if="['first', 'all', 'regex', 'trim'].includes(rule.mode)">
										<span class="text-danger-600">"{{ rule.needle }}"</span>
										<span class="mx-1">→</span>
										<span class="text-create-600">"{{ rule.replacement }}"</span>
									</span>
								</div>
								<div v-if="rule.description" class="text-muted-color/80">{{ rule.description }}</div>
							</div>
						</label>
					</div>
				</div>

				<div class="mt-4 flex gap-2">
					<div class="flex items-center gap-2">
						<label class="text-xs">{{ $t("dialogs.apply_renamer.target") }}</label>
						<SelectButton v-model="target" :options="target_options" option-label="label" option-value="value" :allow-empty="false" />
					</div>
					<div class="flex items-center gap-2">
						<label class="text-xs">{{ $t("dialogs.apply_renamer.scope") }}</label>
						<SelectButton v-model="scope" :options="scope_options" option-label="label" option-value="value" :allow-empty="false" />
					</div>
				</div>
			</div>

			<!-- Step 2: Preview -->
			<div v-else-if="step === 'preview'" class="p-9 text-center text-muted-color">
				<p class="text-sm/8 font-bold mb-4">{{ $t("dialogs.apply_renamer.preview_title") }}</p>

				<div v-if="is_loading_preview" class="flex justify-center py-4">
					<i class="pi pi-spin pi-spinner text-2xl"></i>
				</div>
				<div v-else-if="preview_items.length === 0" class="py-4 text-sm text-muted-color">
					{{ $t("dialogs.apply_renamer.no_changes") }}
				</div>
				<div v-else class="text-left max-h-80 overflow-y-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="border-b border-surface-200 dark:border-surface-700">
								<th class="text-left py-1 px-2">{{ $t("dialogs.apply_renamer.original") }}</th>
								<th class="text-left py-1 px-2">{{ $t("dialogs.apply_renamer.new_title") }}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item in preview_items" :key="item.id" class="border-b border-surface-100 dark:border-surface-800">
								<td class="py-1 px-2 text-danger-600 line-through">{{ item.original }}</td>
								<td class="py-1 px-2 text-create-600">{{ item.new }}</td>
							</tr>
						</tbody>
					</table>
					<p class="text-xs mt-2 text-muted-color">
						{{ sprintf($t("dialogs.apply_renamer.count_changes"), preview_items.length) }}
					</p>
				</div>
			</div>

			<!-- Footer buttons -->
			<div class="flex">
				<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="handleCancel">
					{{ step === "preview" ? $t("dialogs.apply_renamer.back") : $t("dialogs.button.cancel") }}
				</Button>
				<Button
					v-if="step === 'select'"
					severity="contrast"
					class="font-bold w-full border-none rounded-none rounded-br-xl"
					:disabled="selected_rule_ids.length === 0"
					@click="loadPreview"
				>
					{{ $t("dialogs.apply_renamer.preview") }}
				</Button>
				<Button
					v-else
					severity="contrast"
					class="font-bold w-full border-none rounded-none rounded-br-xl"
					:disabled="preview_items.length === 0"
					@click="applyRules"
				>
					{{ $t("dialogs.apply_renamer.apply") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import RenamerService, { type PreviewRenameItem } from "@/services/renamer-service";
import AlbumService from "@/services/album-service";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Dialog from "primevue/dialog";
import SelectButton from "primevue/selectbutton";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const props = defineProps<{
	albumId?: string;
	photoIds?: string[];
	albumIds?: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	applied: [];
}>();

const toast = useToast();

const step = ref<"select" | "preview">("select");
const rules = ref<App.Http.Resources.Models.RenamerRuleResource[]>([]);
const selected_rule_ids = ref<number[]>([]);
const target = ref<"photos" | "albums">("photos");
const scope = ref<"current" | "descendants">("current");
const is_loading_rules = ref(false);
const is_loading_preview = ref(false);
const preview_items = ref<PreviewRenameItem[]>([]);

const target_options = [
	{ label: trans("dialogs.apply_renamer.photos"), value: "photos" },
	{ label: trans("dialogs.apply_renamer.albums"), value: "albums" },
];

const scope_options = [
	{ label: trans("dialogs.apply_renamer.current"), value: "current" },
	{ label: trans("dialogs.apply_renamer.descendants"), value: "descendants" },
];

watch(visible, (new_val) => {
	if (new_val) {
		step.value = "select";
		selected_rule_ids.value = [];
		preview_items.value = [];
		loadRules();
	}
});

function loadRules() {
	is_loading_rules.value = true;
	RenamerService.list().then((response) => {
		rules.value = response.data;
		// Select all rules by default
		selected_rule_ids.value = rules.value.map((r) => r.id);
		is_loading_rules.value = false;
	});
}

function loadPreview() {
	is_loading_preview.value = true;
	step.value = "preview";

	RenamerService.preview({
		album_id: props.albumId,
		target: target.value,
		scope: scope.value,
		rule_ids: selected_rule_ids.value,
		photo_ids: props.photoIds,
		album_ids: props.albumIds,
	}).then((response) => {
		preview_items.value = response.data;
		is_loading_preview.value = false;
	});
}

function applyRules() {
	const ids = preview_items.value.map((item) => item.id);

	const data: { photo_ids?: string[]; album_ids?: string[]; rule_ids: number[] } = {
		rule_ids: selected_rule_ids.value,
	};

	if (target.value === "photos") {
		data.photo_ids = ids;
	} else {
		data.album_ids = ids;
	}

	RenamerService.rename(data).then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.apply_renamer.success"),
			life: 3000,
		});
		AlbumService.clearCache(props.albumId);
		close();
		emits("applied");
	});
}

function handleCancel() {
	if (step.value === "preview") {
		step.value = "select";
	} else {
		close();
	}
}

function close() {
	visible.value = false;
}
</script>
