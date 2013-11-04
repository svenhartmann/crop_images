<?php
namespace Aijko\CropImages\ViewHelpers\Be;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 aijko GmbH <info@aijko.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Loop view helper which can be used to interate over array.
 * Used to create a tab view of an index
 * Implements what a basic foreach()-PHP-method does.
 *
 *
 * @api
 */
class TabContainerViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper implements \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface {

	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @param array $each The array or \TYPO3\CMS\Extbase\Persistence\ObjectStorage to iterated over
	 * @param string $as The name of the iteration variable
	 * @param string $key The name of the variable to store the current array key
	 * @param string $tabTitleField The name of the variable that holds the title for the tab
	 * @param boolean $reverse If enabled, the iterator will start with the last element and proceed reversely
	 * @param string $iteration The name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)
	 * @return string Rendered string
	 * @api
	 */
	public function render($each, $as, $key = '', $tabTitleField = '', $reverse = FALSE, $iteration = NULL) {
		return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @return string
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();
		if ($arguments['each'] === NULL) {
			return '';
		}
		if (is_object($arguments['each']) && !$arguments['each'] instanceof \Traversable) {
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('ForViewHelper only supports arrays and objects implementing \Traversable interface', 1383568822);
		}

		if ($arguments['reverse'] === TRUE) {
			// array_reverse only supports arrays
			if (is_object($arguments['each'])) {
				$arguments['each'] = iterator_to_array($arguments['each']);
			}
			$arguments['each'] = array_reverse($arguments['each']);
		}
		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => count($arguments['each'])
		);

		$menuItems = array();
		$index = 0;
		foreach ($arguments['each'] as $keyValue => $singleElement) {
			$templateVariableContainer->add($arguments['as'], $singleElement);
			if ($arguments['key'] !== '') {
				$templateVariableContainer->add($arguments['key'], $keyValue);
			}
			if ($arguments['iteration'] !== NULL) {
				$iterationData['isFirst'] = $iterationData['cycle'] === 1;
				$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
				$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
				$iterationData['isOdd'] = !$iterationData['isEven'];
				$templateVariableContainer->add($arguments['iteration'], $iterationData);
				$iterationData['index']++;
				$iterationData['cycle']++;
			}
			$menuItems[$index]['label'] = $singleElement[$arguments['tabTitleField']];
			$menuItems[$index]['content'] = $renderChildrenClosure();
			$templateVariableContainer->remove($arguments['as']);
			if ($arguments['key'] !== '') {
				$templateVariableContainer->remove($arguments['key']);
			}
			if ($arguments['iteration'] !== NULL) {
				$templateVariableContainer->remove($arguments['iteration']);
			}
			$index ++;
		}
		$menuId = sha1('Tab Container View Helper');
		$output =  $GLOBALS['TBE_TEMPLATE']->getDynTabMenu($menuItems, $menuId);
		return $output;
	}
}

?>