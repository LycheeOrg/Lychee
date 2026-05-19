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
];
