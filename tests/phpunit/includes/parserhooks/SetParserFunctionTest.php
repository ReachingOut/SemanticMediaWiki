<?php

namespace SMW\Tests;

use SMW\Tests\Utils\UtilityFactory;

use SMW\SetParserFunction;
use SMW\ParameterFormatterFactory;
use SMW\MediaWiki\Renderer\HtmlTemplateRenderer;
use SMW\MediaWiki\Renderer\WikitextTemplateRenderer;
use SMW\ApplicationFactory;

use Title;
use ParserOutput;

/**
 * @covers \SMW\SetParserFunction
 *
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 1.9
 *
 * @author mwjames
 */
class SetParserFunctionTest extends \PHPUnit_Framework_TestCase {

	private $applicationFactory;
	private $semanticDataValidator;

	protected function setUp() {
		parent::setUp();

		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()->newSemanticDataValidator();
		$this->applicationFactory = ApplicationFactory::getInstance();
	}

	protected function tearDown() {
		$this->applicationFactory->clear();

		parent::tearDown();
	}

	public function testCanConstruct() {

		$parserData = $this->getMockBuilder( '\SMW\ParserData' )
			->disableOriginalConstructor()
			->getMock();

		$messageFormatter = $this->getMockBuilder( '\SMW\MessageFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$templateRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlTemplateRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SMW\SetParserFunction',
			new SetParserFunction( $parserData, $messageFormatter, $templateRenderer )
		);
	}

	/**
	 * @dataProvider setParserProvider
	 */
	public function testParse( array $params ) {

		$parserData = $this->applicationFactory->newParserData(
			Title::newFromText( __METHOD__ ),
			new ParserOutput()
		);

		$messageFormatter = $this->getMockBuilder( '\SMW\MessageFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$messageFormatter->expects( $this->any() )
			->method( 'addFromArray' )
			->will( $this->returnSelf() );

		$messageFormatter->expects( $this->once() )
			->method( 'getHtml' )
			->will( $this->returnValue( 'Foo' ) );

		$templateRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlTemplateRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SetParserFunction(
			$parserData,
			$messageFormatter,
			$templateRenderer
		);

		$this->assertInternalType(
			'array',
			$instance->parse( ParameterFormatterFactory::newFromArray( $params ) )
		);
	}

	/**
	 * @dataProvider setParserProvider
	 */
	public function testInstantiatedPropertyValues( array $params, array $expected ) {

		$parserData = $this->applicationFactory->newParserData(
			Title::newFromText( __METHOD__ ),
			new ParserOutput()
		);

		$messageFormatter = $this->getMockBuilder( '\SMW\MessageFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$messageFormatter->expects( $this->any() )
			->method( 'addFromArray' )
			->will( $this->returnSelf() );

		$templateRenderer = $this->getMockBuilder( '\SMW\MediaWiki\Renderer\HtmlTemplateRenderer' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new SetParserFunction(
			$parserData,
			$messageFormatter,
			$templateRenderer
		);

		$instance->parse( ParameterFormatterFactory::newFromArray( $params ) );

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$parserData->getSemanticData()
		);
	}

	public function testTemplateSupport() {

		$params = array( 'Foo=bar', 'Foo=foobar', 'BarFoo=9001', 'template=FooTemplate' );

		$expected = array(
			'errors' => 0,
			'propertyCount'  => 2,
			'propertyLabels' => array( 'Foo', 'BarFoo' ),
			'propertyValues' => array( 'Bar', '9001', 'Foobar' )
		);

		$parserData = $this->applicationFactory->newParserData(
			Title::newFromText( __METHOD__ ),
			new ParserOutput()
		);

		$messageFormatter = $this->getMockBuilder( '\SMW\MessageFormatter' )
			->disableOriginalConstructor()
			->getMock();

		$messageFormatter->expects( $this->any() )
			->method( 'addFromArray' )
			->will( $this->returnSelf() );

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->once() )
			->method( 'recursiveTagParse' )
			->with(
				$this->stringContains(
					'{{FooTemplate|property=Foo|value=bar|last-element=|#=0}}' .
					'{{FooTemplate|property=Foo|value=foobar|last-element=1|#=1}}' .
					'{{FooTemplate|property=BarFoo|value=9001|last-element=1|#=2}}' ) );

		$templateRenderer = new HtmlTemplateRenderer(
			new WikitextTemplateRenderer(),
			$parser
		);

		$instance = new SetParserFunction(
			$parserData,
			$messageFormatter,
			$templateRenderer
		);

		$instance->parse(
			ParameterFormatterFactory::newFromArray( $params )
		);

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$parserData->getSemanticData()
		);
	}

	public function setParserProvider() {

		// #0 Single data set
		// {{#set:
		// |Foo=bar
		// }}
		$provider[] = array(
			array( 'Foo=bar' ),
			array(
				'errors' => 0,
				'propertyCount'  => 1,
				'propertyLabels' => 'Foo',
				'propertyValues' => 'Bar'
			)
		);

		// #1 Empty data set
		// {{#set:
		// |Foo=
		// }}
		$provider[] = array(
			array( 'Foo=' ),
			array(
				'errors' => 0,
				'propertyCount'  => 0,
				'propertyLabels' => '',
				'propertyValues' => ''
			)
		);

		// #2 Multiple data set
		// {{#set:
		// |BarFoo=9001
		// |Foo=bar
		// }}
		$provider[] = array(
			array( 'Foo=bar', 'BarFoo=9001' ),
			array(
				'errors' => 0,
				'propertyCount'  => 2,
				'propertyLabels' => array( 'Foo', 'BarFoo' ),
				'propertyValues' => array( 'Bar', '9001' )
			)
		);

		// #3 Multiple data set with an error record
		// {{#set:
		// |_Foo=9001 --> will raise an error
		// |Foo=bar
		// }}
		$provider[] = array(
			array( 'Foo=bar', '_Foo=9001' ),
			array(
				'errors' => 1,
				'propertyCount'  => 1,
				'propertyLabels' => array( 'Foo' ),
				'propertyValues' => array( 'Bar' )
			)
		);

		return $provider;
	}

}
