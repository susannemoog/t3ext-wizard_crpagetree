<?php declare(strict_types=1);

/***
 *
 * This file is part of the "wizard_crpagetree" extension for TYPO3.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 ***/

namespace MichielRoos\WizardCrpagetree;

use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Tree\View\BrowseTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Creates the "Create pagetree" wizard
 *
 * Old module for TYPO3 6.2, 7.6 and 8.7
 *
 */
class CreatePageTree extends AbstractFunctionModule
{
    /**
     * Main function creating the content for the module.
     *
     * @return   string      HTML content for the module
     */
    public function main()
    {
        $theCode = '';
        $pageTree = [];
        // create new pages here?
        $pRec = BackendUtility::getRecord('pages', $this->pObj->id, 'uid, title', ' AND ' . $GLOBALS['BE_USER']->getPagePermsClause(8));
        $sysPages = GeneralUtility::makeInstance(PageRepository::class);
        $menuItems = $sysPages->getMenu($this->pObj->id);
        if (is_array($pRec)) {
            if (GeneralUtility::_POST('newPageTree') === 'submit') {
                $data = explode("\r\n", GeneralUtility::_POST('data'));
                $data = $this->filterComments($data);
                if (count($data)) {
                    if (GeneralUtility::_POST('createInListEnd')) {
                        $endI = end($menuItems);
                        $thePid = -(int)$endI['uid'];
                        if (!$thePid) {
                            $thePid = $this->pObj->id;
                        }
                    } else {
                        // get parent pid
                        $thePid = $this->pObj->id;
                    }

                    $ic = $this->getIndentationChar();
                    $sc = $this->getSeparationChar();

                    // Reverse the ordering of the data
                    $originalData = $this->getArray($data, 0, $ic);
                    $reversedData = $this->reverseArray($originalData);
                    $data = $this->compressArray($reversedData);

                    if ($data) {
                        $pageIndex = count($data);
                        $sorting = count($data);
                        $oldLevel = 0;
                        $parentPid = [];
                        $currentPid = 0;
                        foreach ($data as $k => $line) {
                            if (trim($line)) {
                                // What level are we on?
                                preg_match('/^' . $ic . '*/', $line, $regs);
                                $level = strlen($regs[0]);

                                if ($level === 0) {
                                    $currentPid = $thePid;
                                    $parentPid[$level] = $thePid;
                                } elseif ($level > $oldLevel) {
                                    $currentPid = 'NEW' . ($pageIndex - 1);
                                    $parentPid[$level] = $pageIndex - 1;
                                } elseif ($level === $oldLevel) {
                                    $currentPid = 'NEW' . $parentPid[$level];
                                } elseif ($level < $oldLevel) {
                                    $currentPid = 'NEW' . $parentPid[$level];
                                }

                                // Get title and additional field values
                                $parts = GeneralUtility::trimExplode($sc, $line);

                                $pageTree['pages']['NEW' . $pageIndex]['title'] = ltrim($parts[0], $ic);
                                $pageTree['pages']['NEW' . $pageIndex]['pid'] = $currentPid;
                                $pageTree['pages']['NEW' . $pageIndex]['sorting'] = $sorting--;
                                $pageTree['pages']['NEW' . $pageIndex]['hidden'] = GeneralUtility::_POST('hidePages') ? 1 : 0;

                                // Drop the title
                                array_shift($parts);

                                // Add additional field values
                                foreach ($this->getExtraFields() as $index => $field) {
                                    $pageTree['pages']['NEW' . $pageIndex][$field] = $parts[$index];
                                }
                                $oldLevel = $level;
                                $pageIndex++;
                            }
                        }
                    }

                    if (count($pageTree['pages'])) {
                        reset($pageTree);
                        $tce = GeneralUtility::makeInstance(DataHandler::class);
                        $tce->stripslashes_values = 0;
                        //reverseOrder does not work with nested arrays
                        //$tce->reverseOrder=1;
                        $tce->start($pageTree, []);
                        $tce->process_datamap();
                        BackendUtility::setUpdateSignal('updatePageTree');
                    } else {
                        $theCode .= $GLOBALS['TBE_TEMPLATE']->rfw($this->getLanguageLabel('noCreate') . '');
                    }

                    // Display result:
                    /** @var BrowseTreeView $tree */
                    $tree = GeneralUtility::makeInstance(BrowseTreeView::class);
                    $tree->init(' AND pages.doktype < 199 AND pages.hidden = "0"');
                    $tree->thisScript = 'index.php';
                    $tree->ext_IconMode = true;
                    $tree->expandAll = true;

                    $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                    $tree->tree[] = [
                        'row'  => $pRec,
                        'HTML' => $iconFactory->getIconForRecord('pages', [$thePid], Icon::SIZE_SMALL)->render()
                    ];
                    $tree->getTree($thePid);

                    $theCode .= $this->getLanguageLabel('created');
                    $theCode .= $tree->printTree();
                }
            } else {
                $theCode .= $this->displayCreateForm();
            }
        } else {
            $theCode .= $GLOBALS['TBE_TEMPLATE']->rfw($this->getLanguageLabel('errorMsg1'));
        }

        // Context Sensitive Help
        $theCode .= BackendUtility::cshItem('_MOD_web_func', 'tx_wizardcrpagetree', $GLOBALS['BACK_PATH'], '|');

        return $this->pObj->doc->section($this->getLanguageLabel('wiz_crMany'), $theCode, 0, 1);
    }

    /**
     * Return the data as a compressed array
     *
     * @param array $data : the uncompressed array
     *
     * @return   array      the data as a compressed array
     */
    private function compressArray($data)
    {
        $newData = [];
        foreach ($data as $value) {
            if ($value['value']) {
                $newData[] = $value['value'];
            }
            if ($value['data']) {
                $newData = array_merge($newData, $this->compressArray($value['data']));
            }
        }

        return $newData;
    }

    /**
     * Return the data as a nested array
     *
     * @param array $data : the data array
     * @param int $oldLevel : the current level
     * @param string $character : indentation character
     *
     * @return   array      the data as a nested array
     */
    private function getArray($data, $oldLevel = 0, $character = ' ')
    {
        $size = count($data);
        $newData = [];
        for ($i = 0; $i < $size;) {
            $regs = [];
            $v = $data[$i];
            if (trim($v)) {
                // What level are we on?
                preg_match('/^' . $character . '*/', $v, $regs);
                $level = strlen($regs[0]);

                if ($level > $oldLevel) {
                    /**
                     * We have entered a sub level. Find the chunk of the array that
                     * constitues this sub level. Pass this chunk to the getArray
                     * function. Then increase the $i to point to the point where the
                     * level is the same as we are on now.
                     */
                    $subData = [];
                    for ($j = $i; $j < $size; $j++) {
                        $regs = [];
                        $v = $data[$j];
                        if (trim($v)) {
                            // What level are we on?
                            preg_match('/^' . $character . '*/', $v, $regs);
                            $subLevel = strlen($regs[0]);
                            if ($subLevel >= $level) {
                                $subData[] = $v;
                            } else {
                                break;
                            }
                        }
                    }
                    $newData[$i - 1]['data'] = $this->getArray($subData, $level, $character);
                    $i = $i + count($subData);
                } elseif (($level == 0) or ($level === $oldLevel)) {
                    $newData[$i]['value'] = $v;
                    $i++;
                }
                $oldLevel = $level;
            }
            if ($i === $size) {
                break;
            }
        }

        return $newData;
    }

    /**
     * Return the data with all the leaves sorted in reverse order
     *
     * @param array $data : input array
     *
     * @return   array      the data reversed
     */
    private function reverseArray($data)
    {
        $newData = [];
        $index = 0;
        foreach ($data as $chunk) {
            if (is_array($chunk['data'])) {
                $newData[$index]['data'] = $this->reverseArray($chunk['data']);
                krsort($newData[$index]['data']);
            }
            $newData[$index]['value'] = $chunk['value'];
            $index++;
        }
        krsort($newData);

        return $newData;
    }

    /**
     * Return the data without comment fields and empty lines
     *
     * @param array $data : input array
     *
     * @return   array      the data reversed
     */
    private function filterComments($data)
    {
        $newData = [];
        $multiLine = false;
        foreach ($data as $value) {
            // Multiline comment
            if (preg_match('#^/\*#', $value) && !$multiLine) {
                $multiLine = true;
                continue;
            }
            if (preg_match('#[\*]+/#', ltrim($value)) && $multiLine) {
                $multiLine = false;
                continue;
            }
            if ($multiLine) {
                continue;
            }
            // Single line comment
            if (preg_match('#^//#', ltrim($value)) || preg_match('/^#/', ltrim($value))
            ) {
                continue;
            }

            // Empty line
            if (!trim($value)) {
                continue;
            }

            $newData[] = $value;
        }

        return $newData;
    }

    /**
     * Return html to display the creation form
     *
     * @return string
     */
    private function displayCreateForm()
    {
        $form = '<h1>' . $this->getLanguageLabel('title') . '</h1>
        <div class="form-group">
            <div class="form-section">
                <div class="row">
                    <div class="form-group col-xs-12">
                        <label for="page_new_0">
                            ' . $this->getLanguageLabel('indentationCharacter') . '
                        </label>
                        <div class="form-control-wrap">
                            <select name="indentationCharacter" class="form-control form-control-adapt">
                                <option value="space" selected="selected">' . $this->getLanguageLabel('indentationSpace') . '</option>
                                <option value="tab">' . $this->getLanguageLabel('indentationTab') . '</option>
                                <option value="dot">' . $this->getLanguageLabel('indentationDot') . '</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="data">
                            ' . $this->getLanguageLabel('howto') . '
                        </label>
                        <div class="form-control-wrap">
                            <textarea class="form-control" id="wiz_newPageTree_data" name="data" ' . $this->pObj->doc->formWidth(35) . ' rows="8"/></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-xs-12">
                        <div class="checkbox">
                            <label for="listEnd"><input type="checkbox" name="createInListEnd" id="listEnd" value="1" />' . $this->getLanguageLabel('listEnd') . '</label>
                        </div>
                        <div class="checkbox">
                            <label for="hidePages"><input type="checkbox" name="hidePages" id="hidePages" value="1" />' . $this->getLanguageLabel('hidePages') . '</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="form-group col-xs-12">
                        <h4>' . $this->getLanguageLabel('advanced') . '</h4>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="extraFields">' . $this->getLanguageLabel('extraFields') . '</label>
                        <input class="form-control" type="text" name="extraFields" size="30" id="extraFields"/>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="separationCharacter">' . $this->getLanguageLabel('separationCharacter') . '</label>
                        <select name="separationCharacter" class="form-control form-control-adapt" id="separationCharacter">
                            <option value="comma" selected="selected">' . $this->getLanguageLabel('separationComma') . '</option>
                            <option value="pipe">' . $this->getLanguageLabel('separationPipe') . '</option>
                            <option value="semicolon">' . $this->getLanguageLabel('separationSemicolon') . '</option>
                            <option value="colon">' . $this->getLanguageLabel('separationColon') . '</option>
                        </select>
                    </div>
                    <input type="hidden" name="newPageTree" value="submit"/>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-xs-12">
                <input class="btn btn-default t3js-wizardcrpages-createnewfields" type="submit" name="create" value="' . $this->getLanguageLabel('lCreate') . '" onclick="return confirm(' . GeneralUtility::quoteJSvalue($this->getLanguageLabel('lCreate_msg1')) . ')">
                <input class="btn btn-default t3js-wizardcrpages-createnewfields" type="reset" value="' . $this->getLanguageLabel('lReset') . '" />
            </div>
        </div>
        ';

        return $form;
    }

    /**
     * Get the indentation character (space, tab or dot)
     *
     * @return   string      the indentation character
     */
    private function getIndentationChar()
    {
        $character = GeneralUtility::_POST('indentationCharacter');
        switch ($character) {
            case 'dot':
                $character = '\.';
                break;
            case 'tab':
                $character = '\t';
                break;
            case 'space':
            default:
                $character = ' ';
                break;
        }

        return $character;
    }

    /**
     * Get the separation character (, or | or ; or :)
     *
     * @return   string      the separation character
     */
    private function getSeparationChar()
    {
        $character = GeneralUtility::_POST('separationCharacter');
        switch ($character) {
            case 'pipe':
                $character = '|';
                break;
            case 'semicolon':
                $character = ';';
                break;
            case 'colon':
                $character = ':';
                break;
            case 'comma':
            default:
                $character = ',';
                break;
        }

        return $character;
    }

    /**
     * Get the extra fields
     *
     * @return array The extra fields
     */
    private function getExtraFields() : array
    {
        $efLine = GeneralUtility::_POST('extraFields');
        if (trim($efLine)) {
            return GeneralUtility::trimExplode(' ', $efLine, 1);
        }

        return [];
    }

    /**
     * Get Language Label
     *
     * @param string $label
     *
     * @return   string      The translated string
     */
    private function getLanguageLabel($label)
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:wizard_crpagetree/Resources/Private/Language/locallang.xlf:' . $label);
    }

    /**
     * Return the helpbubble image tag.
     *
     * @return   string      HTML code for a help-bubble image.
     */
    public function helpBubble()
    {
        return '<img src="' . $GLOBALS['BACK_PATH'] . 'gfx/helpbubble.gif" width="14" height="14" hspace="2" align="top" alt="" />';
    }
}
