<?php

namespace SMW\Tests\Annotator;

use SMW\Annotator\PropertyAnnotatorFactory;

use Title;

/**
 * @covers \SMW\Annotator\PropertyAnnotatorFactory
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PropertyAnnotatorFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\Annotator\PropertyAnnotatorFactory',
			new PropertyAnnotatorFactory()
		);
	}

	public function testNewRedirectPropertyAnnotator() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$redirectTargetFinder = $this->getMockBuilder( '\SMW\MediaWiki\RedirectTargetFinder' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyAnnotatorFactory();

		$this->assertInstanceOf(
			'\SMW\Annotator\RedirectPropertyAnnotator',
			$instance->newRedirectPropertyAnnotator( $semanticData, $redirectTargetFinder )
		);
	}

}
