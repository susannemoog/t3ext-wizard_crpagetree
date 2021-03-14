<?php
namespace MichielRoos\WizardCrpagetree\Hook;

use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class adds WizardCrpagetree related JavaScript to the backend
 */
class BackendControllerHook
{
    /**
     * Adds WizardCrpagetree-specific JavaScript
     *
     * @param array $configuration
     * @param BackendController $backendController
     */
    public function addJavaScript(array $configuration, BackendController $backendController)
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->getPageRenderer()->addInlineSetting('WizardCrpagetree', 'wizardCrpagetreeUrl', (string)$uriBuilder->buildUriFromRoute('pagetree_new'));
    }

    /**
     * @return PageRenderer
     */
    protected function getPageRenderer()
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }
}
