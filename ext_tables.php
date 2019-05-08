<?php
defined('TYPO3_MODE') || die('( ͡ಠ ʖ̯ ͡ಠ)╭∩╮');

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
        'web_func',
        \MichielRoos\WizardCrpagetree\CreatePageTree::class,
        null,
        'LLL:EXT:wizard_crpagetree/Resources/Private/Language/locallang.xlf:title'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        '_MOD_web_func',
        'EXT:wizard_crpagetree/Resources/Private/Language/ContextSensitiveHelp/default.xlf'
    );
}
