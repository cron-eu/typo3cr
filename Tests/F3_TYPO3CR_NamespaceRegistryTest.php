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
 * @subpackage Tests
 * @version $Id$
 */

/**
 * Tests for the NameSpaceRegistry implementation of TYPO3CR
 *
 * @package TYPO3CR
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_TYPO3CR_NamespaceRegistryTest extends F3_Testing_BaseTestCase {

	/**
	 * @var F3_TYPO3CR_NamespaceRegistry
	 */
	protected $namespaceRegistry;

	/**
	 * @var array
	 */
	protected $expectedBuiltInNameSpaces = array(
		'jcr' => 'http://www.jcp.org/jcr/1.0',
		'nt' => 'http://www.jcp.org/jcr/nt/1.0',
		'mix' => 'http://www.jcp.org/jcr/mix/1.0',
		'xml' => 'http://www.w3.org/XML/1998/namespace',
		'' => ''
	);

	/**
	 * Checks if getPrefixes returns the required namespace prefixes according to the specification
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function getPrefixesReturnsRequiredNamepacePrefixes() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$foundPrefixes = array_intersect_assoc(array_keys($this->expectedBuiltInNameSpaces), $namespaceRegistry->getPrefixes());
		$this->assertSame(array_keys($this->expectedBuiltInNameSpaces), $foundPrefixes, 'The NamespaceRegistry did not return the prefixes of the required namespaces.');
	}

	/**
	 * Checks if getPrefix returns the namespace URIs required according to the specification
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function getPrefixReturnsRequiredURI() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		foreach ($this->expectedBuiltInNameSpaces as $prefix => $URI) {
			$this->assertSame($prefix, $namespaceRegistry->getPrefix($URI), 'The NamespaceRegistry did not return the prefix (' . $prefix . ' != ' . $namespaceRegistry->getPrefix($URI) . ') for the requested URI (' . $URI . ').');
		}
	}

	/**
	 * Checks if getURIs returns the required namespace URIs according to the specification
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function getURIsReturnsRequiredNamepaceURIs() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$foundURIs = array_intersect_assoc(array_values($this->expectedBuiltInNameSpaces), $namespaceRegistry->getURIs());
		$this->assertSame(array_values($this->expectedBuiltInNameSpaces), $foundURIs, 'The NamespaceRegistry did not return the URIs of the required namespaces.');
	}

	/**
	 * Checks if getURI returns the namespace prefix required according to the specification
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function getURIReturnsRequiredPrefix() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		foreach ($this->expectedBuiltInNameSpaces as $prefix => $URI) {
			$this->assertSame($URI, $namespaceRegistry->getURI($prefix), 'The NamespaceRegistry did not return the URI (' . $URI . ' != ' . $namespaceRegistry->getURI($prefix) . ') for the requested prefix (' . $prefix . ').');
		}
	}

	/**
	 * Checks if trying to re-register a built-in prefix throws an exception
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function registeringBuiltinPrefixFails() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->never())->method('addNamespace');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		try {
			$namespaceRegistry->registerNamespace('nt', 'http://some.domain.invalid/path');
		} catch (F3_PHPCR_NamespaceException $exception) {
			return;
		}
		$this->fail('registerNamespace threw no exception although a builtin prefix was re-registered.');
	}

	/**
	 * Checks if trying to register a prefix starting with xml throws an exception
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function registeringXMLAsPrefixFails() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->never())->method('addNamespace');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		try {
			$namespaceRegistry->registerNamespace('XmLpf', 'http://some.domain.invalid/path');
		} catch (F3_PHPCR_NamespaceException $exception) {
			return;
		}
		$this->fail('registerNamespace threw no exception although a prefix starting with XmL was registered.');
	}

	/**
	 * Checks if trying to unregister an unknown prefix throws an exception
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function unregisteringUnknownPrefixFails() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->never())->method('deleteNamespace');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		try {
			$namespaceRegistry->unregisterNamespace('probablyUnknownPrefix', 'http://some.domain.invalid/path');
		} catch (F3_PHPCR_NamespaceException $exception) {
			return;
		}
		$this->fail('unregisterNamespace threw no exception although an unknown prefix was unregistered.');
	}

	/**
	 * Checks if a predefined namespace from the DB is available
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function namespaceFromDBIsAvailable() {
		$prefix = 'typo3';
		$uri = 'http://typo3.org/ns/cms/1.0/';

		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->any())->method('getRawNamespaces')->will($this->returnValue(array(array('prefix' => $prefix, 'uri' => $uri))));
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$this->assertSame($prefix, $namespaceRegistry->getPrefix($uri), 'The NamespaceRegistry did not return the URI for a predefined namespace.');
		$this->assertSame($uri, $namespaceRegistry->getURI($prefix), 'The NamespaceRegistry did not return the prefix for a predefined namespace.');
	}

	/**
	 * Registers a namespace, and checks if it is available afterwards
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function registeredNamespaceIsReadable() {
		$prefix = 'testprefix';
		$uri = 'http://5-0.dev.typo3.org/test/1.0/';

		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->once())->method('addNamespace')->with($this->equalTo($prefix), $this->equalTo($uri));
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$namespaceRegistry->registerNamespace($prefix, $uri);
		$this->assertSame($prefix, $namespaceRegistry->getPrefix($uri), 'The NamespaceRegistry did not return the URI for a just registered prefix.');
		$this->assertSame($uri, $namespaceRegistry->getURI($prefix), 'The NamespaceRegistry did not return the prefix for a just registered URI.');
	}

	/**
	 * Checks if re-registering an URI with a new prefix removes the old prefix
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function reregisteringURIRemovesPrefix() {
		$prefix1 = 'testprefix1';
		$prefix2 = 'testprefix2';
		$uri = 'http://5-0.dev.typo3.org/test/1.0/';

		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->once())->method('addNamespace')->with($this->equalTo($prefix1), $this->equalTo($uri));
		$mockStorageAccess->expects($this->once())->method('updateNamespacePrefix')->with($this->equalTo($prefix2), $this->equalTo($uri));
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$namespaceRegistry->registerNamespace($prefix1, $uri);
		$this->assertSame($prefix1, $namespaceRegistry->getPrefix($uri), 'The NamespaceRegistry did not return the URI for a just registered prefix.');
		$this->assertSame($uri, $namespaceRegistry->getURI($prefix1), 'The NamespaceRegistry did not return the prefix for a just registered URI.');

		$namespaceRegistry->registerNamespace($prefix2, $uri);
		$this->assertSame($prefix2, $namespaceRegistry->getPrefix($uri), 'The NamespaceRegistry did not return the URI for a just registered prefix.');
		$this->assertSame($uri, $namespaceRegistry->getURI($prefix2), 'The NamespaceRegistry did not return the prefix for a just registered URI.');

		try {
			$namespaceRegistry->getURI($prefix1);
		} catch (F3_PHPCR_NamespaceException $e) {
			return;
		}
		$this->fail('Re-registering a URI with a new prefix did not remove the old prefix!');
	}

	/**
	 * Checks if re-registering a prefix removes the old URI registered with that prefix
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function reregisteringPrefixRemovesURI() {
		$prefix = 'testprefix3';
		$uri1 = 'http://5-0.dev.typo3.org/test/1.0/';
		$uri2 = 'http://5-0.dev.typo3.org/test/2.0/';

		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->once())->method('addNamespace')->with($this->equalTo($prefix), $this->equalTo($uri1));
		$mockStorageAccess->expects($this->once())->method('updateNamespaceURI')->with($this->equalTo($prefix), $this->equalTo($uri2));
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$namespaceRegistry->registerNamespace($prefix, $uri1);
		$this->assertSame($prefix, $namespaceRegistry->getPrefix($uri1), 'The NamespaceRegistry did not return the URI for a just registered prefix.');
		$this->assertSame($uri1, $namespaceRegistry->getURI($prefix), 'The NamespaceRegistry did not return the prefix for a just registered URI.');

		$namespaceRegistry->registerNamespace($prefix, $uri2);
		$this->assertSame($prefix, $namespaceRegistry->getPrefix($uri2), 'The NamespaceRegistry did not return the URI for a just registered prefix.');
		$this->assertSame($uri2, $namespaceRegistry->getURI($prefix), 'The NamespaceRegistry did not return the prefix for a just registered URI.');

		try {
			$namespaceRegistry->getPrefix($uri1);
		} catch (F3_PHPCR_NamespaceException $e) {
			return;
		}
		$this->fail('Re-registering prefix with a new URI did not remove the old URI!');
	}

	/**
	 * Checks if unregistering a namespace really removes it
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function unregisteringNamespaceRemovedTheNamespace() {
		$prefix = 'testprefix4';
		$uri = 'http://5-0.dev.typo3.org/test/3.0/';

		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->once())->method('addNamespace')->with($this->equalTo($prefix), $this->equalTo($uri));
		$mockStorageAccess->expects($this->once())->method('deleteNamespace')->with($this->equalTo($prefix));
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		$namespaceRegistry->registerNamespace($prefix, $uri);
		$this->assertSame($prefix, $namespaceRegistry->getPrefix($uri), 'The NamespaceRegistry did not return the URI for a just registered prefix.');
		$this->assertSame($uri, $namespaceRegistry->getURI($prefix), 'The NamespaceRegistry did not return the prefix for a just registered URI.');

		$namespaceRegistry->unregisterNamespace($prefix);

		try {
			$namespaceRegistry->getPrefix($uri);
			$namespaceRegistry->getURI($prefix);
		} catch (F3_PHPCR_NamespaceException $e) {
			return;
		}
		$this->fail('Unregistering a namespace did not remove it!');
	}

	/**
	 * Tests whether unregistering a system namespace prefix
	 * throws the expected exception.
	 *
	 * @author Ronny Unger <ru@php-workx.de>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @test
	 */
	public function unregisteringBuiltinNamespacesFails() {
		$mockStorageAccess = $this->getMock('F3_TYPO3CR_Storage_BackendInterface');
		$mockStorageAccess->expects($this->never())->method('deleteNamespace');
		$namespaceRegistry = new F3_TYPO3CR_NamespaceRegistry($mockStorageAccess, $this->componentManager);

		foreach ($this->expectedBuiltInNameSpaces as $prefix => $uri) {
			try {
				$namespaceRegistry->unregisterNamespace($prefix);
				$this->fail("Trying to unregister " . $prefix . " must fail");
			} catch (F3_PHPCR_NamespaceException $e) {
				// expected behaviour
			}
		}
	}

}
?>