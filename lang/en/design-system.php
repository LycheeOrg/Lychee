<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	'title' => 'Design System',
	'description' => 'A live reference for the tokens and components the v8 front end is actually built from. Every swatch and control on this page reads the real CSS custom properties and Nuxt UI components in use today, so it can never drift out of date the way a static mockup would.',

	'sections' => [
		'foundations' => 'Foundations',
		'colors' => 'Color palette',
		'typography' => 'Typography',
		'buttons' => 'Buttons',
		'badges' => 'Badges & chips',
		'forms' => 'Form controls',
		'feedback' => 'Feedback & states',
		'radius' => 'Radius & elevation',
	],

	'loading' => [
		'full_page' => 'Full-page loader',
		'full_page_description' => 'Slow, staggered reveal — used behind the full-screen blackout while a view loads.',
		'mini_spinner' => 'Mini spinner',
		'mini_spinner_description' => 'Fast variant, visible immediately — used inline for buttons, lists, and small loading states.',
	],

	'foundations' => [
		'system' => 'Component kit',
		'system_value' => '@nuxt/ui on Tailwind CSS v4',
		'body_font' => 'Body font',
		'body_font_value' => 'Helvetica Neue, Helvetica, Arial, sans-serif (system stack)',
		'mono_font' => 'Monospace font',
		'mono_font_value' => 'System monospace stack (ui-monospace)',
		'icons' => 'Icon set',
		'icons_value' => 'Lucide, via Iconify (UIcon)',
		'theme' => 'Theme',
		'theme_value' => 'Single dark/light theme, toggled in Settings — no per-section overrides',
	],

	'colors' => [
		'primary' => 'Primary — brand color, primary actions, links, active states',
		'secondary' => 'Secondary — alternate actions and selections',
		'neutral' => 'Neutral — text, borders, surfaces (Slate in light mode, Zinc in dark)',
		'success' => 'Success — confirmations, positive states',
		'warning' => 'Warning — caution, non-blocking issues',
		'error' => 'Error — destructive actions, failures',
		'info' => 'Info — informational messages',
		'resolved_from' => 'Resolved live from :token',
	],

	'typography' => [
		'display' => 'Display',
		'heading' => 'Heading',
		'body' => 'Body text',
		'body_sample' => 'The quick brown fox jumps over the lazy dog — album titles, form labels, and everyday copy render in this stack.',
		'mono' => 'Monospace',
		'mono_sample' => 'file_name.jpg · f/3.5 · ISO 125',
		'roboto' => 'Roboto (utility class)',
		'roboto_sample' => 'Opt-in via the .roboto class, not the default stack',
		'emphasis' => 'Text emphasis scale',
		'emphasis_sample' => 'Photos taken on this day in past years',
	],

	'buttons' => [
		'intro' => 'Every color the button system supports, in the variants actually used across the app.',
	],

	'forms' => [
		'input_label' => 'Album title',
		'input_placeholder' => 'Summer in Kyoto',
		'select_label' => 'Sort by',
		'checkbox_label' => 'Show sensitive albums',
		'switch_label' => 'Public album',
		'switch_description' => 'Anyone with the link can view this album',
		'textarea_label' => 'Description',
		'textarea_placeholder' => 'Add a description for this album…',
		'radio_legend' => 'Tag match logic',
	],

	'feedback' => [
		'alert_info' => 'New photos were added to this album.',
		'alert_success' => 'Settings saved.',
		'alert_warning' => 'This album is visible to anyone with the link.',
		'alert_error' => 'Could not reach the server — changes were not saved.',
		'empty_title' => 'No shares yet',
		'empty_description' => 'Shared albums and their permissions will show up here once you share your first album.',
	],
];
