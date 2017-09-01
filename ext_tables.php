<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

/*
 * FRONTEND PLUGINS
 */

// Facebook
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin($_EXTKEY, 'facebook', 'LLL:EXT:ws_facebook_feed/Resources/Private/Language/locallang_db.xlf:plugin.facebook');

$pluginSignature = str_replace('_', '', $_EXTKEY) . '_facebook';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_facebook.xml');

// TYPOSCRIPT
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Webstobe.Faceboook Feed');