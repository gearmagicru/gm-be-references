<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации установки модуля.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    'use'         => BACKEND,
    'id'          => 'gm.be.references',
    'name'        => 'References',
    'description' => 'Reference static information',
    'expandable'  => true,
    'namespace'   => 'Gm\Backend\References',
    'path'        => '/gm/gm.be.references',
    'route'       => 'references',
    'routes'      => [
        [
            'type'    => 'extensions',
            'options' => [
                'module'      => 'gm.be.references',
                'route'       => 'references[/:extension[/:controller[/:action[/:id]]]]',
                'prefix'      => BACKEND,
                'constraints' => [
                    'id' => '[0-9_-]+'
                ],
                'redirect' => [
                    'info:*@*' => ['info', '*', null]
                ]
            ]
        ]
    ],
    'locales'     => ['ru_RU', 'en_GB'],
    'permissions' => ['any', 'extension', 'info'],
    'events'      => [],
    'required'    => [
        ['php', 'version' => '8.2'],
        ['app', 'code' => 'GM MS'],
        ['app', 'code' => 'GM CMS'],
        ['app', 'code' => 'GM CRM'],
    ]
];
