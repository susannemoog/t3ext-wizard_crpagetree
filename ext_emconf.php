<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Web>Func, Wizards, Create page tree',
    'description' => 'Wizard that will create a page tree for you. Feed it with a space indented tree structure of the desired pages and the pages will be created for you.',
    'category' => 'module',
    'version' => '3.0.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'author' => 'Michiel Roos',
    'author_email' => 'michiel@michielroos.com',
    'author_company' => 'Michiel Roos',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-7.4.0',
            'typo3' => '9.5.0-10.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
