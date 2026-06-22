<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'title' => 'Administrační panel',
    'overview' => 'Přehled',
    'tools' => 'Nástroje',
    'tool_groups' => [
        'core' => 'Správa',
        'monitoring' => 'Monitorování',
        'extensions' => 'Rozšíření',
    ],
    'refresh' => 'Obnovit',
    'metrics' => [
        'photos_count' => 'Fotografie',
        'albums_count' => 'Alba',
        'users_count' => 'Uživatelé',
        'storage_bytes' => 'Využité úložiště',
        'queued_jobs' => 'Úlohy ve frontě',
        'failed_jobs_24h' => 'Neúspěšné úlohy (24 h)',
        'last_successful_job_at' => 'Poslední úspěšná úloha',
    ],
    'errors' => [
        'partial' => 'Některé metriky se nepodařilo načíst.',
    ],
    'security' => [
        'title' => 'Bezpečnostní upozornění',
        'description' => 'Následující zranitelnosti se týkají vaší aktuální verze Lychee. Prosím, proveďte aktualizaci co nejdříve.',
        'no_cvss' => '(bez skóre CVSS)',
    ],
    'update' => [
        'title' => 'Stav aktualizace',
        'update_available' => 'Je k dispozici novější verze (aktuální: :current, nejnovější: :latest).',
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
