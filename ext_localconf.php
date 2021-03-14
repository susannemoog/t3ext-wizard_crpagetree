<?php
defined('TYPO3_MODE') or die('( ͡ಠ ʖ̯ ͡ಠ)╭∩╮');

if (TYPO3_MODE === 'BE' && version_compare(TYPO3_branch, '9.5', '>=')) {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders'][1555874765127] = \MichielRoos\WizardCrpagetree\ContextMenu\ItemProvider::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] = \MichielRoos\WizardCrpagetree\Hook\BackendControllerHook::class . '->addJavaScript';
}
