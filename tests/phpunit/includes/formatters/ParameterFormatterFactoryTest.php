<?php

namespace SMW\Test;

use SMW\ParameterFormatterFactory;

/**
 * Tests for the ParameterFormatterFactory class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\ParameterFormatterFactory
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class ParameterFormatterFactoryTest extends SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\ParameterFormatterFactory';
	}

	/**
	 * Helper method that returns a ArrayFormatter object
	 *
	 * @since 1.9
	 *
	 * @param array $params
	 *
	 * @return ArrayFormatter
	 */
	private function getInstance( array $params = array() ) {
		return ParameterFormatterFactory::newFromArray( $params );
	}

	/**
	 * @test ParameterFormatterFactory::newFromArray
	 *
	 * @since 1.9
	 */
	public function testNewFromArray() {
		$instance = $this->getInstance();
		$this->assertInstanceOf( '\SMW\ArrayFormatter', $instance );
	}
}