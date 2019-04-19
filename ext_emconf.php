<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Web>Func, Wizards, Create page tree',
    'description' => 'Wizard that will create a page tree for you. Feed it with a space indented tree structure of the desired pages and the pages will be created for you.',
    'category' => 'module',
    'shy' => 0,
    'version' => '1.1.0',
    'dependencies' => 'func',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 0,
    'lockType' => '',
    'autoload' => [
        'psr-4' => ['MichielRoos\\WizardCrpagetree\\' => 'Classes'],
        'classmap' => ['Classes']
    ],
    'author' => 'Michiel Roos',
    'author_email' => 'michiel@michielroos.com',
    'author_company' => 'Michiel Roos',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-9.99.99',
            'php' => '5.3.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'suggests' => [],
    '_md5_values_when_last_written' => 's:0:""',
];
