<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3CR;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3CR".                    *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Tests for the PathParser implementation of TYPO3CR
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PathParserTest extends \F3\Testing\BaseTestCase {

	/**
	 * Checks if we receive the root node properly
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function weGetTheRootNode() {
		$mockStorageBackend = $this->getMock('F3\TYPO3CR\Storage\BackendInterface');
		$mockStorageBackend->expects($this->any())->method('getIdentifiersOfSubNodesOfNode')->will($this->returnValue(array()));
		$mockStorageBackend->expects($this->any())->method('getRawNodeType')->will($this->returnValue(array('name' => 'nodeTypeName')));
		$mockRepository = $this->getMock('F3\TYPO3CR\Repository', array(), array(), '', FALSE);
		$mockSession = new \F3\TYPO3CR\Session('workspaceName', $mockRepository, $mockStorageBackend, $this->objectFactory);

		$rawData = array(
			'identifier' => '',
			'parent' => 0,
			'nodetype' => 'nodeTypeName',
			'name' => 'nodeA'
		);
		$rootNode = new \F3\TYPO3CR\Node($rawData, $mockSession, $this->objectFactory);

		$firstNode = \F3\TYPO3CR\PathParser::parsePath('/', $rootNode);
		$this->assertEquals($rootNode, $firstNode, 'The path parser did not return the root node.');

		$secondNode = \F3\TYPO3CR\PathParser::parsePath('/./', $rootNode);
		$this->assertEquals($rootNode, $secondNode, 'The path parser did not return the root node.');
	}

	/**
	 * Checks if we receive a sub node property properly
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function propertiesAreRetrievedCorrectly() {
		$mockRepository = $this->getMock('F3\TYPO3CR\Repository', array(), array(), '', FALSE);
		$mockStorageBackend = new \F3\TYPO3CR\MockStorageBackend();
		$mockStorageBackend->rawRootNodesByWorkspace = array(
			'default' => array(
				'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
				'parent' => 0,
				'nodetype' => 'nt:base',
				'name' => ''
			)
		);
		$mockStorageBackend->rawNodesByIdentifierGroupedByWorkspace = array(
			'default' => array(
				'96bca35d-1ef5-4a47-8b0c-0ddd69507d00' => array(
					'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
					'parent' => 0,
					'nodetype' => 'nt:base',
					'name' => ''
				),
				'96bca35d-1ef5-4a47-8b0c-0ddd69507d10' => array(
					'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d10',
					'parent' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
					'nodetype' => 'nt:base',
					'name' => 'Content'
				),
				'96bca35d-1ef5-4a47-8b0c-0ddd68507d00' => array(
					'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd68507d00',
					'parent' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d10',
					'nodetype' => 'nt:base',
					'name' => 'News'
				),
			)
		);
		$mockStorageBackend->rawPropertiesByIdentifierGroupedByWorkspace = array(
			'default' => array(
				'96bca35d-1ef5-4a47-8b0c-0ddd68507d00' => array(
					array(
						'name' => 'title',
						'value' => 'News about the TYPO3CR',
						'namespace' => '',
						'multivalue' => FALSE,
						'type' => \F3\PHPCR\PropertyType::STRING
					)
				)
			)
		);

		$session = new \F3\TYPO3CR\Session('default', $mockRepository, $mockStorageBackend, $this->objectFactory);
		$rootNode = $session->getRootNode();

		$expectedTitle = 'News about the TYPO3CR';
		$newsItem = \F3\TYPO3CR\PathParser::parsePath('Content/News/title', $rootNode, \F3\TYPO3CR\PathParser::SEARCH_MODE_PROPERTIES);
		$this->assertEquals($expectedTitle, $newsItem->getString(), 'The path parser did not return the expected property value.');
	}

	/**
	 * Checks if we receive the same sub node property twice
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function propertyObjectsAreIdentical() {
		$mockRepository = $this->getMock('F3\TYPO3CR\Repository', array(), array(), '', FALSE);
		$mockStorageBackend = new \F3\TYPO3CR\MockStorageBackend();
		$mockStorageBackend->rawRootNodesByWorkspace = array(
			'default' => array(
				'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
				'parent' => 0,
				'nodetype' => 'nt:base',
				'name' => ''
			)
		);
		$mockStorageBackend->rawNodesByIdentifierGroupedByWorkspace = array(
			'default' => array(
				'96bca35d-1ef5-4a47-8b0c-0ddd69507d00' => array(
					'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
					'parent' => 0,
					'nodetype' => 'nt:base',
					'name' => ''
				),
				'96bca35d-1ef5-4a47-8b0c-0ddd68507d00' => array(
					'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd68507d00',
					'parent' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
					'nodetype' => 'nt:base',
					'name' => 'Node'
				),
			)
		);
		$mockStorageBackend->rawPropertiesByIdentifierGroupedByWorkspace = array(
			'default' => array(
				'96bca35d-1ef5-4a47-8b0c-0ddd68507d00' => array(
					array(
						'name' => 'title',
						'value' => 'Same title, same object!?',
						'namespace' => '',
						'multivalue' => FALSE,
						'type' => \F3\PHPCR\PropertyType::STRING
					)
				)
			)
		);

		$session = new \F3\TYPO3CR\Session('default', $mockRepository, $mockStorageBackend, $this->objectFactory);
		$rootNode = $session->getRootNode();
		$property1 = \F3\TYPO3CR\PathParser::parsePath('Node/title', $rootNode, \F3\TYPO3CR\PathParser::SEARCH_MODE_PROPERTIES);
		$property2 = \F3\TYPO3CR\PathParser::parsePath('Node/title', $rootNode, \F3\TYPO3CR\PathParser::SEARCH_MODE_PROPERTIES);
		$this->assertSame($property1, $property2, 'The path parser did not return the same object.');
	}

	/**
	 * Checks if we receive a sub node properly
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function subnodesAreRetrievedProperly() {
		$expectedContentNodeIdentifier = '96bca35d-1ef5-4a47-8b0c-0ddd69507d10';
		$expectedHomeNodeIdentifier = '96bca35d-1ef5-4a47-8b0c-0ddd68507d00';

		$mockRepository = $this->getMock('F3\TYPO3CR\Repository', array(), array(), '', FALSE);
		$mockStorageBackend = new \F3\TYPO3CR\MockStorageBackend();
		$mockStorageBackend->rawRootNodesByWorkspace = array(
			'default' => array(
				'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
				'parent' => 0,
				'nodetype' => 'nt:base',
				'name' => ''
			)
		);
		$mockStorageBackend->rawNodesByIdentifierGroupedByWorkspace = array(
			'default' => array(
				'96bca35d-1ef5-4a47-8b0c-0ddd69507d00' => array(
					'identifier' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
					'parent' => 0,
					'nodetype' => 'nt:base',
					'name' => ''
				),
				$expectedContentNodeIdentifier => array(
					'identifier' => $expectedContentNodeIdentifier,
					'parent' => '96bca35d-1ef5-4a47-8b0c-0ddd69507d00',
					'nodetype' => 'nt:base',
					'name' => 'Content'
				),
				$expectedHomeNodeIdentifier => array(
					'identifier' => $expectedHomeNodeIdentifier,
					'parent' => $expectedContentNodeIdentifier,
					'nodetype' => 'nt:base',
					'name' => 'Home'
				),
			)
		);

		$session = new \F3\TYPO3CR\Session('default', $mockRepository, $mockStorageBackend, $this->objectFactory);
		$rootNode = $session->getRootNode();

		$node = \F3\TYPO3CR\PathParser::parsePath('/Content', $rootNode);
		$this->assertEquals($expectedContentNodeIdentifier, $node->getIdentifier(), 'The path parser did not return the correct content node.');

		$node = \F3\TYPO3CR\PathParser::parsePath('/Content/', $rootNode);
		$this->assertEquals($expectedContentNodeIdentifier, $node->getIdentifier(), 'The path parser did not return the correct content node.');

		$node = \F3\TYPO3CR\PathParser::parsePath('/Content/.', $rootNode);
		$this->assertEquals($expectedContentNodeIdentifier, $node->getIdentifier(), 'The path parser did not return the correct content node.');

		$node = \F3\TYPO3CR\PathParser::parsePath('Content/..', $rootNode);
		$this->assertEquals($rootNode->getIdentifier(), $node->getIdentifier(), 'The path parser did not return the correct root node.');

		$node = \F3\TYPO3CR\PathParser::parsePath('Content/./Home', $rootNode);
		$this->assertEquals($expectedHomeNodeIdentifier, $node->getIdentifier(), 'The path parser did not return the home page.');
	}

}
?>