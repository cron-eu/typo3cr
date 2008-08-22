<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package TYPO3CR
 * @subpackage Storage
 * @version $Id:F3_TYPO3CR_Storage_BackendInterface.php 888 2008-05-30 16:00:05Z k-fish $
 */

/**
 * Storage search interface
 *
 * @package TYPO3CR
 * @subpackage Storage
 * @version $Id:F3_TYPO3CR_Storage_BackendInterface.php 888 2008-05-30 16:00:05Z k-fish $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface F3_TYPO3CR_Storage_SearchInterface {

	/**
	 * Sets the name of the current workspace
	 *
	 * @param string $workspaceName Name of the workspace which should be used for all search operations
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function setWorkspaceName($workspaceName);

	/**
	 * Performs any needed initialization before the search backend can be used
	 *
	 * @return void
	 */
	public function connect();

	/**
	 * Adds the given node to the index
	 *
	 * @param F3_PHPCR_NodeInterface $node
	 * @return void
	 */
	public function addNode(F3_PHPCR_NodeInterface $node);

	/**
	 * Updates the given node in the index
	 *
	 * @param F3_PHPCR_NodeInterface $node
	 * @return void
	 */
	public function updateNode(F3_PHPCR_NodeInterface $node);

	/**
	 * Deletes the given node from the index
	 *
	 * @param F3_PHPCR_NodeInterface $node
	 * @return void
	 */
	public function deleteNode(F3_PHPCR_NodeInterface $node);

	/**
	 * Returns an array with node identifiers matching the query
	 *
	 * @param F3_PHPCR_Query_QOM_QueryObjectModelInterface $query
	 * @return array
	 */
	public function findNodeIdentifiers(F3_PHPCR_Query_QOM_QueryObjectModelInterface $query);

}
?>