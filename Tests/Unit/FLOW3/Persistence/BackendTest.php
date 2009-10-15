<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3CR\FLOW3\Persistence;

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

require_once(__DIR__ . '/../../Fixtures/AnEntity.php');
require_once(__DIR__ . '/../../Fixtures/AValue.php');

/**
 * Testcase for \F3\TYPO3CR\FLOW3\Persistence\Backend
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class BackendTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeAsksReflectionServiceForClassSchemataAndCallsInternalInitializationMethods() {
		$mockReflectionService = $this->getMock('F3\FLOW3\Reflection\Service');
		$mockReflectionService->expects($this->once())->method('getClassSchemata');
		$backend = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\Backend', array('initializeBaseNode', 'initializeNodeTypes'), array(), '', FALSE);
		$backend->injectReflectionService($mockReflectionService);
		$backend->expects($this->once())->method('initializeBaseNode');
		$backend->expects($this->once())->method('initializeNodeTypes');
		$backend->initialize();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeBaseNodeCreatesNeededNodesIfNotPresent() {
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockPersistenceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockPersistenceNode->expects($this->once())->method('addNode')->with('flow3:objects', 'nt:unstructured')->will($this->returnValue($mockBaseNode));
		$mockRootNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockRootNode->expects($this->exactly(2))->method('hasNode')->will($this->returnValue(FALSE));
		$mockRootNode->expects($this->once())->method('addNode')->with('flow3:persistence', 'nt:unstructured')->will($this->returnValue($mockPersistenceNode));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getRootNode')->will($this->returnValue($mockRootNode));

		$backendClassName = $this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend');
		$backend = new $backendClassName($mockSession);
		$backend->_call('initializeBaseNode', array());

		$this->assertAttributeSame($mockBaseNode, 'baseNode', $backend);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeNodeTypesCreatesNodeTypes() {
		$arrayNodeTypeTemplate = $this->getMock('F3\TYPO3CR\NodeType\NodeTypeTemplate');
		$arrayNodeTypeTemplate->expects($this->once())->method('setName')->with(\F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_ARRAYPROXY);
		$mockNodeTypeManager = $this->getMock('F3\PHPCR\NodeType\NodeTypeManagerInterface');
		$mockNodeTypeManager->expects($this->at(0))->method('hasNodeType')->with(\F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_ARRAYPROXY)->will($this->returnValue(FALSE));
		$mockNodeTypeManager->expects($this->at(1))->method('createNodeTypeTemplate')->will($this->returnValue($arrayNodeTypeTemplate));
		$mockNodeTypeManager->expects($this->at(2))->method('registerNodeType')->with($arrayNodeTypeTemplate, FALSE);
		$objectNodeTypeTemplate = $this->getMock('F3\TYPO3CR\NodeType\NodeTypeTemplate');
		$objectNodeTypeTemplate->expects($this->once())->method('setName')->with(\F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_OBJECTPROXY);
		$mockNodeTypeManager->expects($this->at(3))->method('hasNodeType')->with(\F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_OBJECTPROXY)->will($this->returnValue(FALSE));
		$mockNodeTypeManager->expects($this->at(4))->method('createNodeTypeTemplate')->will($this->returnValue($objectNodeTypeTemplate));
		$mockNodeTypeManager->expects($this->at(5))->method('registerNodeType')->with($objectNodeTypeTemplate, FALSE);
		$splObjectStorageNodeTypeTemplate = $this->getMock('F3\TYPO3CR\NodeType\NodeTypeTemplate');
		$splObjectStorageNodeTypeTemplate->expects($this->once())->method('setName')->with(\F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_SPLOBJECTSTORAGEPROXY);
		$mockNodeTypeManager->expects($this->at(6))->method('hasNodeType')->with(\F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_SPLOBJECTSTORAGEPROXY)->will($this->returnValue(FALSE));
		$mockNodeTypeManager->expects($this->at(7))->method('createNodeTypeTemplate')->will($this->returnValue($splObjectStorageNodeTypeTemplate));
		$mockNodeTypeManager->expects($this->at(8))->method('registerNodeType')->with($splObjectStorageNodeTypeTemplate, FALSE);

		$mockWorkspace = $this->getMock('F3\PHPCR\WorkspaceInterface');
		$mockWorkspace->expects($this->once())->method('getNodeTypeManager')->will($this->returnValue($mockNodeTypeManager));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getWorkspace')->will($this->returnValue($mockWorkspace));

		$backendClassName = $this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend');
		$backend = new $backendClassName($mockSession);
		$backend->_call('initializeNodeTypes');

	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function initializeNodeTypesCreatesNodeTypeFromClassSchema() {
		$classSchemata = array(
			new \F3\FLOW3\Reflection\ClassSchema('Some\Package\SomeClass')
		);
		$nodeTypeTemplate = $this->getMock('F3\TYPO3CR\NodeType\NodeTypeTemplate');
		$nodeTypeTemplate->expects($this->once())->method('setName')->with('flow3:Some_Package_SomeClass');
		$mockNodeTypeManager = $this->getMock('F3\PHPCR\NodeType\NodeTypeManagerInterface');
		$mockNodeTypeManager->expects($this->exactly(4))->method('hasNodeType')->will($this->onConsecutiveCalls(TRUE, TRUE, TRUE, FALSE));
		$mockNodeTypeManager->expects($this->once())->method('createNodeTypeTemplate')->will($this->returnValue($nodeTypeTemplate));
		$mockNodeTypeManager->expects($this->once())->method('registerNodeType')->with($nodeTypeTemplate, FALSE);

		$mockWorkspace = $this->getMock('F3\PHPCR\WorkspaceInterface');
		$mockWorkspace->expects($this->once())->method('getNodeTypeManager')->will($this->returnValue($mockNodeTypeManager));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getWorkspace')->will($this->returnValue($mockWorkspace));

		$backendClassName = $this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend');
		$backend = new $backendClassName($mockSession);
		$backend->_set('classSchemata', $classSchemata);
		$backend->_call('initializeNodeTypes');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function commitDelegatesToPersistObjectsAndProcessDeletedObjectsAndSavesTheSession() {
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('save');

		$backend = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\Backend', array('persistObjects', 'processDeletedObjects'), array($mockSession));
		$backend->expects($this->once())->method('persistObjects');
		$backend->expects($this->once())->method('processDeletedObjects');
		$backend->commit();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectsCreatesNodeOnlyForNewObject() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'F3\\TYPO3CR\\Tests\\' . $className;
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $className . ' {
			public function __construct($FLOW3_Persistence_isNew) { $this->FLOW3_Persistence_isNew = $FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_isNew() { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_isDirty($propertyName) { return FALSE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
			public function FLOW3_AOP_Proxy_getProperty($name) { return NULL; }
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullClassName . '\';}
		}');
		$newObject = new $fullClassName(TRUE);
		$oldObject = new $fullClassName(FALSE);
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($newObject);
		$aggregateRootObjects->attach($oldObject);

		$mockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->once())->method('addNode')->with('flow3:F3_TYPO3CR_Tests_' . $className, 'flow3:F3_TYPO3CR_Tests_' . $className)->will($this->returnValue($mockInstanceNode));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($oldObject, '');

		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('finalizeObjectProxyNodes', 'persistObject'), array($mockSession));
		$backend->expects($this->once())->method('finalizeObjectProxyNodes');
		$backend->expects($this->exactly(2))->method('persistObject');
		$backend->injectIdentityMap($identityMap);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_set('classSchemata', array($fullClassName => new \F3\FLOW3\Reflection\ClassSchema($fullClassName)));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @see http://forge.typo3.org/issues/show/3859
	 */
	public function persistObjectsHandlesCyclicReferences() {
		$namespace = 'F3\TYPO3CR\Tests';
		$className1 = 'SomeClass' . uniqid();
		$fullClassName1 = $namespace . '\\' . $className1;
		$JCRName1 = str_replace('\\', '_', $fullClassName1);
		eval('namespace ' . $namespace . '; class ' . $className1 . ' implements \F3\FLOW3\AOP\ProxyInterface {
			protected $FLOW3_Persistence_isNew = TRUE;
			protected $FLOW3_Persistence_Entity_UUID = \'A\';
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_isDirty($propertyName) { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) { $this->FLOW3_Persistence_isNew = FALSE; }
		}');
		$className2 = 'SomeClass' . uniqid();
		$fullClassName2 = $namespace . '\\' . $className2;
		$JCRName2 = str_replace('\\', '_', $fullClassName2);
		eval('namespace ' . $namespace . '; class ' . $className2 . ' implements \F3\FLOW3\AOP\ProxyInterface {
			protected $FLOW3_Persistence_isNew = TRUE;
			protected $FLOW3_Persistence_Entity_UUID = \'B\';
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_isDirty($propertyName) { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) { $this->FLOW3_Persistence_isNew = FALSE; }
		}');
		$className3 = 'SomeClass' . uniqid();
		$fullClassName3 = $namespace . '\\' . $className3;
		$JCRName3 = str_replace('\\', '_', $fullClassName3);
		eval('namespace ' . $namespace . '; class ' . $className3 . ' implements \F3\FLOW3\AOP\ProxyInterface {
			protected $FLOW3_Persistence_isNew = TRUE;
			protected $FLOW3_Persistence_Entity_UUID = \'C\';
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_isDirty($propertyName) { return $this->FLOW3_Persistence_isNew; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) { $this->FLOW3_Persistence_isNew = FALSE; }
		}');
		$objectA = new $fullClassName1();
		$objectB = new $fullClassName2();
		$objectC = new $fullClassName3();
		$objectA->sub = $objectB;
		$objectB->sub = $objectC;
		$objectC->sub = $objectB;
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($objectA);

		$classSchema1 = new \F3\FLOW3\Reflection\ClassSchema($fullClassName1);
		$classSchema1->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema1->addProperty('sub', $fullClassName2);
		$classSchema1->setAggregateRoot(TRUE);
		$classSchema2 = new \F3\FLOW3\Reflection\ClassSchema($fullClassName2);
		$classSchema2->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema2->addProperty('sub', $fullClassName3);
		$classSchema3 = new \F3\FLOW3\Reflection\ClassSchema($fullClassName3);
		$classSchema3->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema3->addProperty('sub', $fullClassName2);

		$mockProxyNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockProxyNode->expects($this->any())->method('getIdentifier')->will($this->returnValue('PROXY'));
		$mockProxyNode->expects($this->once())->method('setProperty')->with('flow3:target', 'B', \F3\PHPCR\PropertyType::REFERENCE);
		$mockCNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockCNode->expects($this->any())->method('getIdentifier')->will($this->returnValue('C'));
		$mockCNode->expects($this->once())->method('addNode')->with('flow3:sub', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_OBJECTPROXY)->will($this->returnValue($mockProxyNode));
		$mockBNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBNode->expects($this->any())->method('getIdentifier')->will($this->returnValue('B'));
		$mockBNode->expects($this->once())->method('addNode')->with('flow3:sub', 'flow3:' . $JCRName3, 'C')->will($this->returnValue($mockCNode));
		$mockANode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockANode->expects($this->any())->method('getIdentifier')->will($this->returnValue('A'));
		$mockANode->expects($this->once())->method('addNode')->with('flow3:sub', 'flow3:' . $JCRName2, 'B')->will($this->returnValue($mockBNode));
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->any())->method('getIdentifier')->will($this->returnValue('BASE'));
		$mockBaseNode->expects($this->once())->method('addNode')->with('flow3:' . $JCRName1, 'flow3:' . $JCRName1, 'A')->will($this->returnValue($mockANode));

		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->at(0))->method('getNodeByIdentifier')->with('A')->will($this->returnValue($mockANode));
		$mockSession->expects($this->at(1))->method('getNodeByIdentifier')->with('B')->will($this->returnValue($mockBNode));
		$mockSession->expects($this->at(2))->method('getNodeByIdentifier')->with('C')->will($this->returnValue($mockCNode));

		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->injectIdentityMap(new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap());
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_set('classSchemata', array($fullClassName1 => $classSchema1, $fullClassName2 => $classSchema2, $fullClassName3 => $classSchema3));
		$backend->_set('baseNode', $mockBaseNode);

		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function uuidPropertyNameFromNewObjectIsUsedForNode() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'F3\\TYPO3CR\\Tests\\' . $className;
		$identifier = \F3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $className . ' {
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return FALSE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullClassName . '\'; }
			public function FLOW3_AOP_Proxy_getProperty($name) { return \'' . $identifier . '\'; }
		}');
		$newObject = new $fullClassName();
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($newObject);

		$mockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->once())->method('addNode')->with('flow3:F3_TYPO3CR_Tests_' . $className, 'flow3:F3_TYPO3CR_Tests_' . $className, $identifier)->will($this->returnValue($mockInstanceNode));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$classSchema = new \F3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->addProperty('idProp', 'string');
		$classSchema->setUUIDPropertyName('idProp');

		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('finalizeObjectProxyNodes', 'persistObject'), array($mockSession));
		$backend->expects($this->once())->method('finalizeObjectProxyNodes');
		$backend->expects($this->once())->method('persistObject');
		$backend->injectIdentityMap(new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap());
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_set('classSchemata', array($fullClassName => $classSchema));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectProcessesDirtyObject() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'F3\\TYPO3CR\\Tests\\' . $className;
		$identifier = \F3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $className . ' {
			public $simpleString = \'simpleValue\';
			protected $dirty = TRUE;
			public function FLOW3_Persistence_isNew() { return FALSE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return $this->dirty; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) { $this->dirty = FALSE; }
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullClassName . '\'; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
		}');
		$dirtyObject = new $fullClassName();

		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockInstanceNode->expects($this->once())->method('setProperty')->with('flow3:simpleString', 'simpleValue', \F3\PHPCR\PropertyType::STRING);
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->with($identifier)->will($this->returnValue($mockInstanceNode));

		$classSchema = new \F3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('simpleString', 'string');
		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($dirtyObject, $identifier);

		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->injectIdentityMap($identityMap);
		$backend->_set('classSchemata', array($fullClassName => $classSchema));
		$backend->_set('baseNode', $mockBaseNode);

		$this->assertTrue($dirtyObject->FLOW3_Persistence_isDirty('simpleString'));
		$backend->_call('persistObject', $dirtyObject);
		$this->assertFalse($dirtyObject->FLOW3_Persistence_isDirty('simpleString'));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function processDeletedObjectsRemovesNodeAndUnregistersObjectWithIdentityMap() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'F3\\TYPO3CR\\Tests\\' . $className;
		$identifier = \F3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $className . ' {
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return NULL; }
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullClassName . '\'; }
		}');
		$deletedObject = new $fullClassName();
		$deletedObjects = new \SplObjectStorage();
		$deletedObjects->attach($deletedObject);

		$mockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockInstanceNode->expects($this->once())->method('remove');
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->with($identifier)->will($this->returnValue($mockInstanceNode));
		$identityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap', array('unregisterObject'));
		$identityMap->expects($this->once())->method('unregisterObject')->with($deletedObject);
		$identityMap->registerObject($deletedObject, $identifier);

		$backendClassName = $this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend');
		$backend = new $backendClassName($mockSession);
		$backend->injectIdentityMap($identityMap);
		$backend->setDeletedObjects($deletedObjects);
		$backend->_call('processDeletedObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function nestedObjectsAreStoredAsNestedNodes() {
			// set up object
		$A = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Entity_UUID = NULL;
		$B = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('B');
		$B->FLOW3_Persistence_Entity_UUID = NULL;
		$BA = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('BA');
		$BA->FLOW3_Persistence_Entity_UUID = NULL;
		$B->add($BA);
		$A->add($B);
		$C = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('C');
		$C->FLOW3_Persistence_Entity_UUID = NULL;
		$A->add($C);
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($A);

			// set up assertions on created nodes
		$mockNodeBA = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeBA->expects($this->exactly(1))->method('setProperty');
		$arrayPropertyProxyB = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayPropertyProxyB->expects($this->once())->method('addNode')->will($this->returnValue($mockNodeBA));
		$mockNodeB = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeB->expects($this->exactly(1))->method('addNode')->will($this->returnValue($arrayPropertyProxyB));
		$mockNodeB->expects($this->exactly(1))->method('setProperty')->with('flow3:name', 'B', \F3\PHPCR\PropertyType::STRING);
		$mockNodeC = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeC->expects($this->exactly(1))->method('setProperty');
		$arrayPropertyProxyA = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayPropertyProxyA->expects($this->exactly(2))->method('addNode')->will($this->onConsecutiveCalls($mockNodeB, $mockNodeC));
		$mockNodeA = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeA->expects($this->exactly(1))->method('addNode')->will($this->returnValue($arrayPropertyProxyA));
		$mockNodeA->expects($this->exactly(1))->method('setProperty')->with('flow3:name', 'A', \F3\PHPCR\PropertyType::STRING);
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->once())->method('addNode')->with('flow3:F3_TYPO3CR_Tests_Fixtures_AnEntity', 'flow3:F3_TYPO3CR_Tests_Fixtures_AnEntity')->will($this->returnValue($mockNodeA));

			// set up needed infrastructure
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->exactly(4))->method('getNodeByIdentifier')->will($this->onConsecutiveCalls($mockNodeA, $mockNodeB, $mockNodeBA, $mockNodeC));
		$classSchema = new \F3\FLOW3\Reflection\ClassSchema('F3\TYPO3CR\Tests\AnObject');
		$classSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('name', 'string');
		$classSchema->addProperty('members', 'array');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('finalizeObjectProxyNodes'), array($mockSession));
		$backend->injectIdentityMap(new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap());
		$backend->_set('classSchemata', array('F3\TYPO3CR\Tests\Fixtures\AnEntity' => $classSchema));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectProcessesNewObjectsWithDateTimeMember() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'F3\\TYPO3CR\\Tests\\' . $className;
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $className . ' {
			public $date;
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullClassName . '\';}
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
		}');
		$newObject = new $fullClassName();
		$date = new \DateTime();
		$newObject->date = $date;
		$newObject->FLOW3_Persistence_Entity_UUID = NULL;

		$mockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockInstanceNode->expects($this->never())->method('addNode');
		$mockInstanceNode->expects($this->once())->method('setProperty')->with('flow3:date', $date, \F3\PHPCR\PropertyType::DATE);
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->will($this->returnValue($mockInstanceNode));
		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($newObject, '');
		$classSchema = new \F3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('date', 'DateTime');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->injectIdentityMap($identityMap);
		$backend->_set('classSchemata', array($fullClassName => $classSchema));
		$backend->_call('persistObject', $newObject);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function valueObjectsAreStoredAsOftenAsUsedInAnEntity() {
			// set up object
		$A = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Entity_UUID = NULL;
		$B = new \F3\TYPO3CR\Tests\Fixtures\AValue('B');
		$B->FLOW3_Persistence_Entity_UUID = NULL;
		$A->add($B);
		$A->add($B);
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($A);

			// set up assertions on created nodes
		$mockNodeB1 = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeB1->expects($this->once())->method('setProperty')->with('flow3:name', 'B', \F3\PHPCR\PropertyType::STRING);
		$mockNodeB2 = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeB2->expects($this->once())->method('setProperty')->with('flow3:name', 'B', \F3\PHPCR\PropertyType::STRING);
		$arrayPropertyProxy = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayPropertyProxy->expects($this->exactly(2))->method('addNode')->will($this->onConsecutiveCalls($mockNodeB1, $mockNodeB2));
		$arrayPropertyProxy->expects($this->at(0))->method('addNode')->with('flow3:0');
		$arrayPropertyProxy->expects($this->at(1))->method('addNode')->with('flow3:1');
		$mockNodeA = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeA->expects($this->once())->method('addNode')->will($this->returnValue($arrayPropertyProxy));
		$mockNodeA->expects($this->once())->method('setProperty')->with('flow3:name', 'A', \F3\PHPCR\PropertyType::STRING);

			// set up needed infrastructure
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->will($this->returnValue($mockNodeA));
		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($A, '');
		$entityClassSchema = new \F3\FLOW3\Reflection\ClassSchema('F3\TYPO3CR\Tests\Fixture\AnEntity');
		$entityClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$entityClassSchema->addProperty('name', 'string');
		$entityClassSchema->addProperty('members', 'array');
		$valueClassSchema = new \F3\FLOW3\Reflection\ClassSchema('F3\TYPO3CR\Tests\Fixture\AValue');
		$valueClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
		$valueClassSchema->addProperty('name', 'string');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->injectIdentityMap($identityMap);
		$backend->_set('classSchemata', array('F3\TYPO3CR\Tests\Fixtures\AnEntity' => $entityClassSchema, 'F3\TYPO3CR\Tests\Fixtures\AValue' => $valueClassSchema));
		$backend->_call('persistObject', $A);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function aValueObjectIsStoredAsOftenAsUsedInEntities() {
			// set up object
		$A = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Entity_UUID = NULL;
		$B = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('B');
		$B->FLOW3_Persistence_Entity_UUID = NULL;
		$value = new \F3\TYPO3CR\Tests\Fixtures\AValue('value');
		$A->setValue($value);
		$B->setValue($value);
		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($A);
		$aggregateRootObjects->attach($B);

			// set up assertions on created nodes
		$mockNodeValue1 = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeValue1->expects($this->once())->method('setProperty')->with('flow3:name', 'value', \F3\PHPCR\PropertyType::STRING);
		$mockNodeValue2 = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeValue2->expects($this->once())->method('setProperty')->with('flow3:name', 'value', \F3\PHPCR\PropertyType::STRING);
		$mockNodeA = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeA->expects($this->once())->method('addNode')->with('flow3:value', 'flow3:F3_TYPO3CR_Tests_Fixtures_AValue')->will($this->returnValue($mockNodeValue1));
		$mockNodeA->expects($this->once())->method('setProperty')->with('flow3:name', 'A', \F3\PHPCR\PropertyType::STRING);
		$mockNodeB = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeB->expects($this->once())->method('addNode')->with('flow3:value', 'flow3:F3_TYPO3CR_Tests_Fixtures_AValue')->will($this->returnValue($mockNodeValue2));
		$mockNodeB->expects($this->once())->method('setProperty')->with('flow3:name', 'B', \F3\PHPCR\PropertyType::STRING);
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->exactly(2))->method('addNode')->with('flow3:F3_TYPO3CR_Tests_Fixtures_AnEntity', 'flow3:F3_TYPO3CR_Tests_Fixtures_AnEntity')->will($this->onConsecutiveCalls($mockNodeA, $mockNodeB));

			// set up needed infrastructure
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->exactly(4))->method('getNodeByIdentifier')->will($this->onConsecutiveCalls($mockNodeA, $mockNodeValue1, $mockNodeB, $mockNodeValue2));
		$entityClassSchema = new \F3\FLOW3\Reflection\ClassSchema('F3\TYPO3CR\Tests\Fixtures\AnEntity');
		$entityClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$entityClassSchema->addProperty('name', 'string');
		$entityClassSchema->addProperty('value', 'F3\TYPO3CR\Tests\Fixtures\AValue');
		$valueClassSchema = new \F3\FLOW3\Reflection\ClassSchema('F3\TYPO3CR\Tests\Fixture\AValue');
		$valueClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_VALUEOBJECT);
		$valueClassSchema->addProperty('name', 'string');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('finalizeObjectProxyNodes'), array($mockSession));
		$backend->injectIdentityMap(new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap());
		$backend->_set('classSchemata', array('F3\TYPO3CR\Tests\Fixtures\AnEntity' => $entityClassSchema, 'F3\TYPO3CR\Tests\Fixtures\AValue' => $valueClassSchema));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_call('persistObjects');
	}

	/**
	 * Does it return the UUID for an object know to the identity map?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsUUIDForKnownObject() {
		$knownObject = new \stdClass();
		$fakeUUID = '123-456';

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasObject')->with($knownObject)->will($this->returnValue(TRUE));
		$mockIdentityMap->expects($this->once())->method('getIdentifierByObject')->with($knownObject)->will($this->returnValue($fakeUUID));
		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertEquals($fakeUUID, $backend->getIdentifierByObject($knownObject));
	}

	/**
	 * Does it return the UUID for an AOP proxy not being in the identity map
	 * but having FLOW3_Persistence_Entity_UUID?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsUUIDForObjectBeingAOPProxy() {
		$knownObject = $this->getMock('F3\FLOW3\AOP\ProxyInterface');
		$knownObject->expects($this->once())->method('FLOW3_AOP_Proxy_hasProperty')->with('FLOW3_Persistence_Entity_UUID')->will($this->returnValue(TRUE));
		$knownObject->expects($this->once())->method('FLOW3_AOP_Proxy_getProperty')->with('FLOW3_Persistence_Entity_UUID')->will($this->returnValue('fakeUUID'));
		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasObject')->with($knownObject)->will($this->returnValue(FALSE));

		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertEquals('fakeUUID', $backend->getIdentifierByObject($knownObject));
	}

	/**
	 * Does it work for objects not being an AOP proxy, i.e. not having the
	 * method FLOW3_AOP_Proxy_getProperty() and not known to the identity map?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsNullForUnknownObjectBeingPOPO() {
		$unknownObject = new \stdClass();
		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasObject')->with($unknownObject)->will($this->returnValue(FALSE));

		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertNull($backend->getIdentifierByObject($unknownObject));
	}

	/**
	 * Does it return NULL for an AOP proxy not being in the identity map and
	 * not having FLOW3_Persistence_Entity_UUID?
	 *
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsNullForUnknownObjectBeingAOPProxy() {
		$unknownObject = $this->getMock('F3\FLOW3\AOP\ProxyInterface');
		$unknownObject->expects($this->once())->method('FLOW3_AOP_Proxy_hasProperty')->with('FLOW3_Persistence_Entity_UUID')->will($this->returnValue(FALSE));
		$unknownObject->expects($this->never())->method('FLOW3_AOP_Proxy_getProperty');
		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasObject')->with($unknownObject)->will($this->returnValue(FALSE));

		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertNull($backend->getIdentifierByObject($unknownObject));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectByIdentifierReturnsObjectFromIdentityMapForKnownIdentifier() {
		$knownObject = new \stdClass();
		$fakeUUID = '123-456';

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasIdentifier')->with($fakeUUID)->will($this->returnValue(TRUE));
		$mockIdentityMap->expects($this->once())->method('getObjectByIdentifier')->with($fakeUUID)->will($this->returnValue($knownObject));
		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertEquals($knownObject, $backend->getObjectByIdentifier($fakeUUID));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectByIdentifierReturnsObjectFromRepositoryForKnownIdentifier() {
		$knownObject = new \stdClass();
		$fakeUUID = '123-456';

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasIdentifier')->with($fakeUUID)->will($this->returnValue(FALSE));
		$mockNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->with($fakeUUID)->will($this->returnValue($mockNode));
		$mockDataMapper = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\DataMapper');
		$mockDataMapper->expects($this->once())->method('mapSingleNode')->with($mockNode)->will($this->returnValue($knownObject));
		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($mockSession);
		$backend->injectIdentityMap($mockIdentityMap);
		$backend->injectDataMapper($mockDataMapper);

		$this->assertSame($knownObject, $backend->getObjectByIdentifier($fakeUUID));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectByIdentifierReturnsNullForUnknownIdentifier() {
		$fakeUUID = '123-456';

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasIdentifier')->with($fakeUUID)->will($this->returnValue(FALSE));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->with($fakeUUID)->will($this->throwException(new \F3\PHPCR\ItemNotFoundException()));
		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($mockSession);
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertNull($backend->getObjectByIdentifier($fakeUUID));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function isNewObjectReturnsTrueIfTheObjectHasNoUUIDYet() {
		$object = new \stdClass();

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasObject')->with($object)->will($this->returnValue(FALSE));
		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertTrue($backend->isNewObject($object));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function isNewObjectReturnsFalseIfTheObjectDoesHaveAUUID() {
		$object = new \stdClass();

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('hasObject')->with($object)->will($this->returnValue(TRUE));
		$backend = new \F3\TYPO3CR\FLOW3\Persistence\Backend($this->getMock('F3\PHPCR\SessionInterface'));
		$backend->injectIdentityMap($mockIdentityMap);

		$this->assertFalse($backend->isNewObject($object));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function replaceObjectUnregistersTheExistingObjectAndRegistersTheNewObjectAtTheIdentityMap() {
		$existingObject = new \stdClass();
		$newObject = new \stdClass();

		$mockIdentityMap = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\IdentityMap');
		$mockIdentityMap->expects($this->once())->method('unregisterObject')->with($existingObject);
		$mockIdentityMap->expects($this->once())->method('registerObject')->with($newObject, 'the uuid');

		$backend = $this->getMock('F3\TYPO3CR\FLOW3\Persistence\Backend', array('getIdentifierByObject'), array(), '', FALSE);
		$backend->expects($this->once())->method('getIdentifierByObject')->with($existingObject)->will($this->returnValue('the uuid'));
		$backend->injectIdentityMap($mockIdentityMap);
		$backend->replaceObject($existingObject, $newObject);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function interAggregateReferencesAreStoredAsObjectProxyNodes() {
			// set up objects
		$authorClassName = uniqid('Author');
		$qualifiedAuthorClassName = 'F3\\' . $authorClassName;
		eval('namespace F3; class ' . $authorClassName . ' implements \F3\FLOW3\AOP\ProxyInterface {
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return NULL; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {} }');
		$author = new $qualifiedAuthorClassName;
		$postClassName = uniqid('Post');
		$qualifiedPostClassName = 'F3\\' . $postClassName;
		eval('namespace F3; class ' . $postClassName . ' implements \F3\FLOW3\AOP\ProxyInterface {
			public $author;
			public $FLOW3_Persistence_Entity_UUID;
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {} }');
		$post = new $qualifiedPostClassName();
		$post->author = $author;

		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($post);
		$aggregateRootObjects->attach($author);

			// set up assertions on created nodes
		$mockAuthorProxyNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockPostNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockPostNode->expects($this->at(2))->method('addNode')->with('flow3:author', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_OBJECTPROXY)->will($this->returnValue($mockAuthorProxyNode));
		$mockPostNode->expects($this->any())->method('getIdentifier')->will($this->returnValue(\F3\FLOW3\Utility\Algorithms::generateUUID()));
		$mockAuthorNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockAuthorNode->expects($this->any())->method('getIdentifier')->will($this->returnValue(\F3\FLOW3\Utility\Algorithms::generateUUID()));
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->at(0))->method('addNode')->with('flow3:F3_' . $postClassName, 'flow3:F3_' . $postClassName)->will($this->returnValue($mockPostNode));
		$mockBaseNode->expects($this->at(1))->method('addNode')->with('flow3:F3_' . $authorClassName, 'flow3:F3_' . $authorClassName)->will($this->returnValue($mockAuthorNode));

			// set up needed infrastructure
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->exactly(2))->method('getNodeByIdentifier')->will($this->onConsecutiveCalls($mockPostNode, $mockAuthorNode));
		$postClassSchema = new \F3\FLOW3\Reflection\ClassSchema($qualifiedPostClassName);
		$postClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$postClassSchema->setAggregateRoot(TRUE);
		$postClassSchema->addProperty('author', $qualifiedAuthorClassName);
		$authorClassSchema = new \F3\FLOW3\Reflection\ClassSchema($qualifiedAuthorClassName);
		$authorClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$authorClassSchema->setAggregateRoot(TRUE);

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('finalizeObjectProxyNodes'), array($mockSession));
		$backend->injectIdentityMap(new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap());
		$backend->_set('classSchemata', array($qualifiedPostClassName => $postClassSchema, $qualifiedAuthorClassName => $authorClassSchema));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function bidirectionalInterAggregateReferencesAreStoredAsObjectProxyNodes() {
			// set up objects
		$postClassName = uniqid('Post');
		$qualifiedPostClassName = 'F3\\' . $postClassName;
		eval('namespace F3; class ' . $postClassName . ' implements \F3\FLOW3\AOP\ProxyInterface {
			public $blog;
			public $FLOW3_Persistence_Entity_UUID = NULL;
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {} }');
		$blogClassName = uniqid('Blog');
		$qualifiedBlogClassName = 'F3\\' . $blogClassName;
		eval('namespace F3; class ' . $blogClassName . ' implements \F3\FLOW3\AOP\ProxyInterface {
			public $post;
			public $FLOW3_Persistence_Entity_UUID = NULL;
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {} }');
		$post = new $qualifiedPostClassName;
		$blog = new $qualifiedBlogClassName();
		$blog->post = $post;
		$post->blog = $blog;

		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($blog);
		$aggregateRootObjects->attach($post);

			// set up assertions on created nodes
		$mockBlogNodeUUID = \F3\FLOW3\Utility\Algorithms::generateUUID();
		$mockPostNodeUUID = \F3\FLOW3\Utility\Algorithms::generateUUID();
		$mockBlogProxyNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBlogProxyNode->expects($this->at(0))->method('setProperty')->with('flow3:target', $mockBlogNodeUUID, \F3\PHPCR\PropertyType::REFERENCE);
		$mockPostProxyNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockPostProxyNode->expects($this->at(0))->method('setProperty')->with('flow3:target', $mockPostNodeUUID, \F3\PHPCR\PropertyType::REFERENCE);
		$mockPostNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockPostNode->expects($this->at(2))->method('addNode')->with('flow3:blog', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_OBJECTPROXY)->will($this->returnValue($mockBlogProxyNode));
		$mockPostNode->expects($this->any())->method('getIdentifier')->will($this->returnValue($mockPostNodeUUID));
		$mockBlogNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBlogNode->expects($this->at(2))->method('addNode')->with('flow3:post', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_OBJECTPROXY)->will($this->returnValue($mockPostProxyNode));
		$mockBlogNode->expects($this->any())->method('getIdentifier')->will($this->returnValue($mockBlogNodeUUID));
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->at(0))->method('addNode')->with('flow3:F3_' . $blogClassName, 'flow3:F3_' . $blogClassName)->will($this->returnValue($mockBlogNode));
		$mockBaseNode->expects($this->at(1))->method('addNode')->with('flow3:F3_' . $postClassName, 'flow3:F3_' . $postClassName)->will($this->returnValue($mockPostNode));

			// set up needed infrastructure
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->exactly(2))->method('getNodeByIdentifier')->will($this->onConsecutiveCalls($mockBlogNode, $mockPostNode));
		$blogClassSchema = new \F3\FLOW3\Reflection\ClassSchema($qualifiedBlogClassName);
		$blogClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$blogClassSchema->setAggregateRoot(TRUE);
		$blogClassSchema->addProperty('post', $qualifiedPostClassName);
		$postClassSchema = new \F3\FLOW3\Reflection\ClassSchema($qualifiedPostClassName);
		$postClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$postClassSchema->setAggregateRoot(TRUE);
		$postClassSchema->addProperty('blog', $qualifiedBlogClassName);

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->injectIdentityMap(new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap());
		$backend->_set('classSchemata', array($qualifiedBlogClassName => $blogClassSchema, $qualifiedPostClassName => $postClassSchema));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function SplObjectStoragePropertyIsStoredAsProxyNode() {
			// set up object
		$A = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('A');
		$A->FLOW3_Persistence_Entity_UUID = NULL;
		$B = new \F3\TYPO3CR\Tests\Fixtures\AnEntity('B');
		$B->FLOW3_Persistence_Entity_UUID = NULL;
		$A->addObject($B);

			// set up assertions on created nodes
		$itemNode = $this->getMock('F3\PHPCR\NodeInterface');
		$proxyNode = $this->getMock('F3\PHPCR\NodeInterface');
		$proxyNode->expects($this->once())->method('addNode')->with('flow3:item', 'nt:unstructured')->will($this->returnValue($itemNode));
		$mockNodeA = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeA->expects($this->once())->method('addNode')->with('flow3:objects', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_SPLOBJECTSTORAGEPROXY)->will($this->returnValue($proxyNode));
		$mockNodeA->expects($this->once())->method('setProperty')->with('flow3:name', 'A', \F3\PHPCR\PropertyType::STRING);
		$mockNodeB = $this->getMock('F3\PHPCR\NodeInterface');
		$mockNodeB->expects($this->once())->method('setProperty')->with('flow3:name', 'B', \F3\PHPCR\PropertyType::STRING);

			// set up needed infrastructure
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->exactly(2))->method('getNodeByIdentifier')->will($this->onConsecutiveCalls($mockNodeA, $mockNodeB));
		$classSchema = new \F3\FLOW3\Reflection\ClassSchema('F3\TYPO3CR\Tests\AnEntity');
		$classSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('name', 'string');
		$classSchema->addProperty('objects', 'SplObjectStorage');
		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($A, '');
		$identityMap->registerObject($B, '');

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('createNodeForEntity'), array($mockSession));
		$backend->expects($this->once())->method('createNodeForEntity')->with($B, $itemNode, 'flow3:object');
		$backend->injectIdentityMap($identityMap);
		$backend->_set('classSchemata', array('F3\TYPO3CR\Tests\Fixtures\AnEntity' => $classSchema));
		$backend->_call('persistObject', $A);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function objectsDetachedFromSplObjectStorageAreRemovedFromRepository() {
		$object = $this->getMock('stdClass', array('FLOW3_AOP_Proxy_getProperty'));
		$object->expects($this->once())->method('FLOW3_AOP_Proxy_getProperty')->with('FLOW3_Persistence_Entity_UUID')->will($this->returnValue('fakeUUID'));

		$objectStorage = new \SplObjectStorage();
		$objectStorage->attach($object);
		$previousObjectStorage = clone $objectStorage;
		$objectStorage->detach($object);

		$targetProperty = $this->getMock('F3\PHPCR\PropertyInterface');
		$targetProperty->expects($this->once())->method('getString')->will($this->returnValue('fakeUUID'));
		$objectNode = $this->getMock('F3\PHPCR\NodeInterface');
		$objectNode->expects($this->once())->method('getProperty')->with('flow3:target')->will($this->returnValue($targetProperty));
		$proxyNode = $this->getMock('F3\PHPCR\NodeInterface');
		$proxyNode->expects($this->once())->method('getNode')->with('flow3:object')->will($this->returnValue($objectNode));
		$proxyNode->expects($this->once())->method('remove');

		$nodeIterator = array($proxyNode);

		$fooNode = $this->getMock('F3\PHPCR\NodeInterface');
		$fooNode->expects($this->once())->method('getNodes')->will($this->returnValue($nodeIterator));

		$parentNode = $this->getMock('F3\PHPCR\NodeInterface');
		$parentNode->expects($this->once())->method('hasNode')->with('foo')->will($this->returnValue(TRUE));
		$parentNode->expects($this->once())->method('getNode')->with('foo')->will($this->returnValue($fooNode));

			// ... and here we go
		$queue = array();
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array(), '', FALSE);
		$backend->_call('persistSplObjectStorage', $objectStorage, $parentNode, 'foo', $queue, $previousObjectStorage);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistArrayWorksRecursively() {

		$arrayProxyNodeB = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayProxyNodeB->expects($this->once())->method('setProperty')->with('flow3:bar', 'value', \F3\PHPCR\PropertyType::STRING);
		$arrayProxyNodeA = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayProxyNodeA->expects($this->once())->method('hasNode')->with('flow3:foo')->will($this->returnValue(FALSE));
		$arrayProxyNodeA->expects($this->once())->method('addNode')->with('flow3:foo', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_ARRAYPROXY)->will($this->returnValue($arrayProxyNodeB));
		$parentNode = $this->getMock('F3\PHPCR\NodeInterface');
		$parentNode->expects($this->once())->method('hasNode')->with('flow3:arrayProperty')->will($this->returnValue(FALSE));
		$parentNode->expects($this->once())->method('addNode')->with('flow3:arrayProperty', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_ARRAYPROXY)->will($this->returnValue($arrayProxyNodeA));
		$array = array('foo' => array('bar' => 'value'));
		$queue = array();

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($this->getMock('F3\PHPCR\SessionInterface')));
		$backend->_call('persistArray', $array, $parentNode, 'flow3:arrayProperty', $queue);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistArrayStoresRecursiveArraysWithLeafsBeingObjects() {
		$objectClassName = uniqid('Object');
		eval('namespace {
				class ' . $objectClassName . ' {
					public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return get_class($this); }
					public function FLOW3_Persistence_isNew() { return TRUE; }
				}
			}');
		$object = new $objectClassName();
		$classSchema = new \F3\FLOW3\Reflection\ClassSchema($objectClassName);
		$classSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');

		$arrayProxyNodeB = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayProxyNodeA = $this->getMock('F3\PHPCR\NodeInterface');
		$arrayProxyNodeA->expects($this->once())->method('hasNode')->with('flow3:foo')->will($this->returnValue(FALSE));
		$arrayProxyNodeA->expects($this->once())->method('addNode')->with('flow3:foo', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_ARRAYPROXY)->will($this->returnValue($arrayProxyNodeB));
		$parentNode = $this->getMock('F3\PHPCR\NodeInterface');
		$parentNode->expects($this->once())->method('hasNode')->with('flow3:arrayProperty')->will($this->returnValue(FALSE));
		$parentNode->expects($this->once())->method('addNode')->with('flow3:arrayProperty', \F3\TYPO3CR\FLOW3\Persistence\Backend::NODETYPE_ARRAYPROXY)->will($this->returnValue($arrayProxyNodeA));
		$array = array('foo' => array('bar' => $object));
		$queue = array();

			// ... and here we go
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('createNodeForEntity'), array($mockSession));
		$backend->expects($this->once())->method('createNodeForEntity')->with($object, $arrayProxyNodeB, 'flow3:bar');
		$backend->_set('classSchemata', array($objectClassName => $classSchema));
		$backend->_call('persistArray', $array, $parentNode, 'flow3:arrayProperty', $queue);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function aggregateRootObjectsFoundWhenPersistingThatAreNotAmongAggregateRootObjectsCollectedFromRepositoriesArePersisted() {
		$otherClassName = 'OtherClass' . uniqid();
		$fullOtherClassName = 'F3\\TYPO3CR\\Tests\\' . $otherClassName;
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $otherClassName . ' implements \F3\FLOW3\AOP\ProxyInterface {
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullOtherClassName . '\';}
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return NULL; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
		}');
		$someClassName = 'SomeClass' . uniqid();
		$fullSomeClassName = 'F3\\TYPO3CR\\Tests\\' . $someClassName;
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $someClassName . ' implements \F3\FLOW3\AOP\ProxyInterface {
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullSomeClassName . '\';}
			public function FLOW3_AOP_Proxy_construct() {}
			public function FLOW3_AOP_Proxy_invokeJoinPoint(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {}
			public function FLOW3_AOP_Proxy_hasProperty($propertyName) { return TRUE; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
			public function FLOW3_AOP_Proxy_setProperty($propertyName, $value) {}
			public function FLOW3_Persistence_isNew() { return TRUE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return TRUE; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) {}
		}');
		$otherAggregateRootObject = new $fullOtherClassName();
		$someAggregateRootObject = new $fullSomeClassName();
		$someAggregateRootObject->property = $otherAggregateRootObject;

		$otherClassSchema = new \F3\FLOW3\Reflection\ClassSchema($otherClassName);
		$otherClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$otherClassSchema->setAggregateRoot(TRUE);
		$someClassSchema = new \F3\FLOW3\Reflection\ClassSchema($someClassName);
		$someClassSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$someClassSchema->setAggregateRoot(TRUE);
		$someClassSchema->addProperty('property', $fullOtherClassName);

		$aggregateRootObjects = new \SplObjectStorage();
		$aggregateRootObjects->attach($someAggregateRootObject);

		$otherMockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$otherMockInstanceNode->expects($this->any())->method('getIdentifier')->will($this->returnValue('otherUUID'));
		$someMockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$someMockInstanceNode->expects($this->once())->method('addNode')->will($this->returnValue($this->getMock('F3\PHPCR\NodeInterface')));
		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockBaseNode->expects($this->once())->method('addNode')->will($this->returnValue($otherMockInstanceNode));
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->at(0))->method('getNodeByIdentifier')->will($this->returnValue($someMockInstanceNode));
		$mockSession->expects($this->at(1))->method('getNodeByIdentifier')->with('otherUUID')->will($this->returnValue($otherMockInstanceNode));

		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($someAggregateRootObject, '');

		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->injectIdentityMap($identityMap);
		$backend->setAggregateRootObjects($aggregateRootObjects);
		$backend->_set('classSchemata', array(
			$fullOtherClassName => $otherClassSchema,
			$fullSomeClassName => $someClassSchema
		));
		$backend->_set('baseNode', $mockBaseNode);
		$backend->_call('persistObjects');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function persistObjectCallsCheckPropertyType() {
		$className = 'SomeClass' . uniqid();
		$fullClassName = 'F3\\TYPO3CR\\Tests\\' . $className;
		$identifier = \F3\FLOW3\Utility\Algorithms::generateUUID();
		eval('namespace F3\\TYPO3CR\\Tests; class ' . $className . ' {
			public $simpleString = \'simpleValue\';
			protected $dirty = TRUE;
			public function FLOW3_Persistence_isNew() { return FALSE; }
			public function FLOW3_Persistence_isDirty($propertyName) { return $this->dirty; }
			public function FLOW3_Persistence_memorizeCleanState($propertyName = NULL) { $this->dirty = FALSE; }
			public function FLOW3_AOP_Proxy_getProxyTargetClassName() { return \'' . $fullClassName . '\'; }
			public function FLOW3_AOP_Proxy_getProperty($propertyName) { return $this->$propertyName; }
		}');
		$object = new $fullClassName();

		$mockBaseNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockInstanceNode = $this->getMock('F3\PHPCR\NodeInterface');
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$mockSession->expects($this->once())->method('getNodeByIdentifier')->with($identifier)->will($this->returnValue($mockInstanceNode));

		$classSchema = new \F3\FLOW3\Reflection\ClassSchema($fullClassName);
		$classSchema->setModelType(\F3\FLOW3\Reflection\ClassSchema::MODELTYPE_ENTITY);
		$classSchema->addProperty('simpleString', 'string');
		$identityMap = new \F3\TYPO3CR\FLOW3\Persistence\IdentityMap();
		$identityMap->registerObject($object, $identifier);

		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('checkPropertyType'), array($mockSession));
		$backend->injectIdentityMap($identityMap);
		$backend->_set('classSchemata', array($fullClassName => $classSchema));
		$backend->_set('baseNode', $mockBaseNode);

			// ... and here we go
		$backend->expects($this->once())->method('checkPropertyType')->with('string', 'simpleValue');
		$backend->_call('persistObject', $object);
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function checkPropertyTypeReturnsForNullValue() {
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->_call('checkPropertyType', 'dummy', NULL);
	}

	/**
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function typesAndInvalidValuesForCheckPropertyType() {
		return array(
			array('string', 1),
			array('string', array()),
			array('string', new \stdClass()),
			array('string', FALSE),
			array('array', 'foo'),
			array('array', 1),
			array('array', TRUE),
			array('DateTime', 1),
			array('DateTime', ''),
			array('DateTime', FALSE),
			array('DateTime', new \stdClass()),
		);
	}

	/**
	 * @test
	 * @expectedException \F3\TYPO3CR\FLOW3\Persistence\Exception\UnexpectedTypeException
	 * @dataProvider typesAndInvalidValuesForCheckPropertyType
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function checkPropertyTypeThrowsExceptionOnMismatch($type, $value) {
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->_call('checkPropertyType', $type, $value);
	}

	/**
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function typesAndValidValuesForCheckPropertyType() {
		return array(
			array('string', ''),
			array('string', 'foo'),
			array('boolean', FALSE),
			array('array', array()),
			array('DateTime', new \DateTime),
			array('stdClass', new \stdClass()),
		);
	}

	/**
	 * @test
	 * @dataProvider typesAndValidValuesForCheckPropertyType
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function checkPropertyTypeReturnsOnMatch($type, $value) {
		$mockSession = $this->getMock('F3\PHPCR\SessionInterface');
		$backend = $this->getMock($this->buildAccessibleProxy('F3\TYPO3CR\FLOW3\Persistence\Backend'), array('dummy'), array($mockSession));
		$backend->_call('checkPropertyType', $type, $value);
	}
}

?>