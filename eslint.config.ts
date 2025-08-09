import { globalIgnores } from "eslint/config";
import { defineConfigWithVueTs, vueTsConfigs } from "@vue/eslint-config-typescript";
import pluginVue from "eslint-plugin-vue";
import pluginVitest from "@vitest/eslint-plugin";
import skipFormatting from "@vue/eslint-config-prettier/skip-formatting";
import noRelativeImportPaths from "eslint-plugin-no-relative-import-paths";

// To allow more languages other than `ts` in `.vue` files, uncomment the following lines:
// import { configureVueProject } from '@vue/eslint-config-typescript'
// configureVueProject({ scriptLangs: ['ts', 'tsx'] })
// More info at https://github.com/vuejs/eslint-config-typescript/#advanced-setup

export default defineConfigWithVueTs(
	{
		name: "app/files-to-lint",
		files: ["**/*.{ts,mts,tsx,vue}"],
	},

	globalIgnores(["**/dist/**", "**/dist-ssr/**", "**/coverage/**", "**/vendor/webauthn/**"]),

	pluginVue.configs["flat/essential"],
	//   pluginVue.configs['flat/recommended'],
	//   pluginVue.configs['flat/strongly-recommended'],
	vueTsConfigs.recommended,
	{
		plugins: {
			"no-relative-import-paths": noRelativeImportPaths,
		},
		rules: {
			"no-relative-import-paths/no-relative-import-paths": "error",
		},
	},
	{
		...pluginVitest.configs.recommended,
		files: ["src/**/__tests__/*"],
	},
	skipFormatting,
	{
		rules: {
			"vue/multi-word-component-names": "off",
			"@typescript-eslint/no-unused-vars": [
				"error",
				{
					args: "all",
					argsIgnorePattern: "^_",
					caughtErrors: "all",
					caughtErrorsIgnorePattern: "^_",
					destructuredArrayIgnorePattern: "^_",
					varsIgnorePattern: "^_",
					ignoreRestSiblings: true,
				},
			],
			"no-relative-import-paths/no-relative-import-paths": ["error", { allowSameFolder: true, rootDir: "resources/js", prefix: "@" }],
		},
	},
);
