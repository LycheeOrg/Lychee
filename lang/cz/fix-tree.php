<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Údržba',
    'intro' => 'Na této stránce můžete ručně měnit pořadí a opravovat svá alba.<br />Před provedením jakýchkoli změn důrazně doporučujeme seznámit se s informacemi o Vnořených stromových strukturách.',
    'warning' => 'Zde můžete vážně poškodit svou instalaci Lychee, úpravy provádíte na vlastní riziko.',

    'help' => [
        'header' => 'Nápověda',
        'hover' => 'Přejděte kurzorem na ID nebo názvy a zvýrazněte související alba.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Vlevo</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Vpravo</span>',
        'convenience' => 'Pro vaše pohodlí vám tlačítka <i class="pi pi-angle-up" ></i> a <i class="pi pi-angle-down" ></i> umožňují změnit hodnoty %s a %s o +1, respektive -1 s propagací.',
        'left-right-warn' => 'Tlačítka <i class="text-warning-600 pi pi-chevron-circle-left" ></i> a <i class="text-warning-600 pi pi-chevron-circle-right" ></i> označují, že hodnota %s (resp. %s) je někde duplicitní.',
        'parent-marked' => 'Označené <span class="font-bold text-danger-600">ID rodiče</span> naznačuje, že %s a %s nesplňují podmínky pro Vnořené stromové struktury. Upravte buď <span class="font-bold text-danger-600">ID rodiče</span>, nebo hodnoty %s/%s.',
        'slowness' => 'Tato stránka bude při velkém počtu alb pomalá.',
    ],
    'buttons' => [
        'reset' => 'Resetovat',
        'check' => 'Zkontrolovat',
        'apply' => 'Použít',
    ],
    'no-changes' => 'Žádné změny.',
    'table' => [
        'title' => 'Název',
        'left' => 'Vlevo',
        'right' => 'Vpravo',
        'id' => 'Id',
        'parent' => 'Rodič Id',
    ],
    'errors' => [
        'invalid' => 'Neplatný strom!',
        'invalid_details' => 'Tuto změnu neprovádíme, protože se jedná o zaručeně nefunkční stav.',
        'invalid_left' => 'Album %s má neplatnou hodnotu vlevo.',
        'invalid_right' => 'Album %s má neplatnou hodnotu vpravo.',
        'invalid_left_right' => 'Album %s má neplatné hodnoty vlevo/vpravo. Hodnota vlevo by měla být striktně menší než hodnota vpravo: %s < %s.',
        'duplicate_left' => 'Album %s má duplicitní hodnotu vlevo %s.',
        'duplicate_right' => 'Album %s má duplicitní hodnotu vpravo %s.',
        'parent' => 'Album %s má neočekávané ID rodiče %s.',
        'unknown' => 'Album %s má neznámou chybu.',
    ],
];
