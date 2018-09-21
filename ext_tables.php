<?php
defined('TYPO3_MODE') || die('¯\_(ツ)_/¯');

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (TYPO3_MODE === 'BE') {
    ExtensionManagementUtility::insertModuleFunction(
        'web_func',
        \MichielRoos\WizardCrpagetree\CreatePageTree::class,
        null,
        'LLL:EXT:wizard_crpagetree/Resources/Private/Language/locallang.xml:wiz_crPageTree'
    );
    ExtensionManagementUtility::addLLrefForTCAdescr(
        '_MOD_web_func',
        'EXT:wizard_crpagetree/Resources/Private/Language/ContextSensitiveHelp/default.xml'
    );
}
