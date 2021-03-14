/**
 * Module: TYPO3/CMS/WizardCrpagetree/ContextMenuActions
 *
 * JavaScript to handle wizard create pagetree actions from context menu
 * @exports TYPO3/CMS/WizardCrpagetree/ContextMenuActions
 */

define(["require", "exports", "jquery"], (function (e, t, a) {
	"use strict";
	return new class {
		pagesNewTree(e, t) {
			const n = "pages" === e ? t : a.default(this).data("page-uid");
			"pages" === e ? top.TYPO3.Backend.ContentContainer.setUrl(top.TYPO3.settings.WizardCrpagetree.wizardCrpagetreeUrl + "&id=" + n) : '';
		}
	}
}));

