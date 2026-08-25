<?php

declare(strict_types=1);

return [
    'cotec_spreadsheet' => [
        'spreadsheet_id' => env('GOOGLE_SHEETS_COTEC_SPREADSHEET_ID', '1pcjdC19nNJAPKIYCirgwIBZIJsBrcFuCTpDEOUbpPOw'),

        'sheets' => [
            615480757 => [
                'key' => 'demanda-de-construcao',
                'name' => 'DEMANDA DE CONSTRUÇÃO',
            ],
            1355441995 => [
                'key' => 'caderno',
                'name' => 'Caderno',
            ],
            62463680 => [
                'key' => 'backup',
                'name' => 'BACKUP',
            ],
            1142334527 => [
                'key' => 'rotas',
                'name' => 'ROTAS',
            ],
            1964615295 => [
                'key' => 'reformas',
                'name' => 'Reformas',
            ],
            941971074 => [
                'key' => 'tamanhos',
                'name' => 'TAMANHOS',
            ],
            1426277740 => [
                'key' => 'pesquisa',
                'name' => 'PESQUISA',
            ],
            2106669123 => [
                'key' => 'caderno-tecnico',
                'name' => 'CADERNO TÉCNICO',
            ],
        ],
    ],

    'contract_spreadsheet' => [
        'spreadsheet_id' => env(
            'GOOGLE_SHEETS_CONTRACT_SPREADSHEET_ID',
            '1sBia2FTJW2dQWOqVGUsj_pm_C2t-2v1eEjfuJeTXV60',
        ),

        'sheets' => [
            'contracts' => [
                'gid' => 539442641,
                'name' => ' GERENCIADORA',
                'range' => 'A:Z',
                'header_row' => 1,
            ],
            'value-additives' => [
                'gid' => 866864035,
                'name' => 'ADITIVO DE VALOR  -OBRAS',
                'range' => 'A:Z',
                'header_row' => 1,
            ],
            'readjustments' => [
                'gid' => 432939090,
                'name' => 'REAJUSTES E REEQUILÍBRIO',
                'range' => 'A:Z',
                'header_row' => 1,
            ],
            'execution-deadlines' => [
                'gid' => 1217858042,
                'name' => 'CONTROLE PRAZOS DE EXECUÇÃO -  ',
                'range' => 'A:Z',
                'header_row' => 1,
            ],
        ],
    ],
];
