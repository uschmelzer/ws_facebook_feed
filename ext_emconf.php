<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Facebook',
    'description' => 'This plugin loads a facebook feed and displays the recent entries',
    'category' => 'plugin',
    'author' => 'Cornel Widmer',
    'author_email' => 'cornel@webstobe.ch',
    'author_company' => 'Webstobe GmbH',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '1.0.1',
    'constraints' => array(
        'depends' => array(
            'extbase' => '7.6.0-8.7.0',
            'fluid' => '7.6.0-8.7.0',
            'typo3' => '7.6.0-8.7.0',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);