<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Admin Dashboard',
    'overview' => 'Overview',
    'tools' => 'Tools',
    'tool_groups' => [
        'core' => 'Admin',
        'monitoring' => 'Monitoring',
        'extensions' => 'Extensions',
    ],
    'refresh' => 'Refresh',
    'metrics' => [
        'photos_count' => 'Photos',
        'albums_count' => 'Albums',
        'users_count' => 'Users',
        'storage_bytes' => 'Storage used',
        'queued_jobs' => 'Queued jobs',
        'failed_jobs_24h' => 'Failed jobs (24h)',
        'last_successful_job_at' => 'Last successful job',
    ],
    'errors' => [
        'partial' => 'Some metrics could not be loaded.',
    ],
    'security' => [
        'title' => 'Security Advisories',
        'description' => 'The following vulnerabilities affect your current Lychee version. Please update as soon as possible.',
        'no_cvss' => '(no CVSS score)',
    ],
    'update' => [
        'title' => 'Update Status',
        'update_available' => 'A newer version is available (current: :current, latest: :latest).',
    ],
    'nsfw_config' => [
        'title' => 'NSFW Detection & Moderation',
        'tab_settings' => 'Settings',
        'tab_presets' => 'Presets',
        'description' => 'Configure automated NSFW content detection for uploaded photos. Photos are scanned asynchronously via the external classification service.',
        'enable' => 'Enable NSFW Classification',
        'preset' => 'Detection Preset',
        'scan_trusted' => 'Scan Trusted Users',
        'section_actions' => 'Actions',
        'block_check' => 'Block finding: Check users',
        'block_monitor' => 'Block finding: Monitor users',
        'block_tbv' => 'Block finding: Trust-but-verify users',
        'block_trusted' => 'Block finding: Trusted users',
        'sensitive_album' => 'Sensitive: album action',
        'sensitive_no_album' => 'Sensitive: no album fallback',
        'section_hide_on_scan' => 'Hide During Scan',
        'hide_on_scan_warning' => 'When enabled, photos are hidden (placed in moderation queue) while the NSFW scan is in progress. If the NSFW classifier crashes or is unavailable, the photo will remain hidden until manually approved by an admin.',
        'hide_monitor' => 'Hide Monitor photos during scan',
        'hide_tbv' => 'Hide Trust-but-verify photos during scan',
        'hide_trusted' => 'Hide Trusted photos during scan',
        'section_matrix' => 'Trust-Tier × Finding Matrix',
        'matrix_trust_level' => 'Trust Level',
        'matrix_check' => 'Check',
        'matrix_monitor' => 'Monitor',
        'matrix_tbv' => 'Trust but verify',
        'matrix_trusted' => 'Trusted',
        'runtime_config' => 'Service Runtime Config',
        'presets' => 'Available Presets',
        'key' => 'Key',
        'value' => 'Value',
        'active' => 'Active',
        'block' => 'Block',
        'review' => 'Review',
        'sensitive' => 'Sensitive',
        'none' => 'None',
        'matrix_block_moderate' => 'Block/Moderate',
        'matrix_block_moderate_approve' => 'Block/Moderate/Approve',
        'matrix_moderate' => 'Moderate',
        'matrix_approve' => 'Approve',
        'matrix_moderate_album' => 'Moderate + Album',
        'matrix_album_or_nothing' => 'Album/Nothing',
    ],
];
