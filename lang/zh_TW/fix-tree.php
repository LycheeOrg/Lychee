<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Fix-tree Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Maintenance',
	'intro' => 'This page allows you to re-order and fix your albums manually.<br />Before any modifications, we strongly recommend you to read about Nested Set tree structures.',
	'warning' => 'You can really break your Lychee installation here, modify values at your own risks.',

	'help' => [
		'header' => 'Help',
		'hover' => 'Hover ids or titles to highlight related albums.',
		'left' => '<span class="text-muted-color-emphasis font-bold">Left</span>',
		'right' => '<span class="text-muted-color-emphasis font-bold">Right</span>',
		'convenience' => 'For your convenience, the <i class="pi pi-angle-up" ></i> and <i class="pi pi-angle-down" ></i> buttons allow you to change the values of %s and %s by respectively +1 and -1 with propagation.',
		'left-right-warn' => 'The <i class="text-warning-600 pi pi-chevron-circle-left" ></i> and <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indicates that the value of %s (and respectively %s) is duplicated somewhere.',
		'parent-marked' => 'Marked <span class="font-bold text-danger-600">Parent Id</span> indicates that the %s and %s do not satisfy the Nest Set tree structures. Edit either the <span class="font-bold text-danger-600">Parent Id</span> or the %s/%s values.',
		'slowness' => 'This page will be slow with a large number of albums.',
	],

	'buttons' => [
		'reset' => 'Reset',
		'check' => 'Check',
		'apply' => 'Apply',
	],

	'table' => [
		'title' => 'Title',
		'left' => 'Left',
		'right' => 'Right',
		'id' => 'Id',
		'parent' => 'Parent Id',
	],

	'errors' => [
		'invalid' => 'Invalid tree!',
		'invalid_details' => 'We are not applying this as it is guaranteed to be a broken state.',
		'invalid_left' => 'Album %s has an invalid left value.',
		'invalid_right' => 'Album %s has an invalid right value.',
		'invalid_left_right' => 'Album %s has an invalid left/right values. Left should be strictly smaller than right: %s < %s.',
		'duplicate_left' => 'Album %s has a duplicate left value %s.',
		'duplicate_right' => 'Album %s has a duplicate right value %s.',
		'parent' => 'Album %s has an unexpected parent id %s.',
		'unknown' => 'Album %s has an unknown error.',
	],
];