<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin('Webstobe.' . $_EXTKEY, 'Facebook', array(
    'Facebook' => 'show,info',
), // non-cacheable actions
    array(
        'Facebook' => 'show,info',
    ));

// REGISTER NEW CONTENT TYPES
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ws_facebook_feed/Configuration/TSconfig/ContentElementWizard.txt">');

// REGISTER TASKS
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Webstobe\\WsFacebookFeed\\Tasks\\Feed'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:ws_facebook_feed/Resources/Private/Language/locallang_db.xlf:task.feed.title',
    'description' => 'Beschreibung.',
    'additionalFields' => 'Webstobe\\WsFacebookFeed\\Tasks\\FeedAdditionalFields'
);

if (TYPO3_MODE === 'BE') {
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('ext-wsfacebook-wizard-icon', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, ['source' => 'EXT:ws_facebook_feed/Resources/Public/Icons/FacebookWizicon.svg']);
}