<?php

namespace SMW\Test;

use SMWHooks;
use User;
use Title;
use WikiPage;
use ParserOutput;
use Parser;
use LinksUpdate;

/**
 * Tests for the SMW\Hooks class
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 1.9
 *
 * @file
 * @ingroup SMW
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */

/**
 * This class is testing implemented hooks and verifies consistency with its
 * invoked methods to ensure a hook generally returns true.
 *
 * @ingroup SMW
 * @ingroup Test
 */
class HooksTest extends \MediaWikiTestCase {

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function getTextDataProvider() {
		return array(
			array(
				"[[Lorem ipsum]] dolor sit amet, consetetur sadipscing elitr, sed diam " .
				" nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat."
			),
		);
	}

	/**
	 * Helper method to normalize a path
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	private function normalizePath( $path ) {
		return str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $path );
	}

	/**
	 * Helper method that returns a random string
	 *
	 * @since 1.9
	 *
	 * @param $length
	 *
	 * @return string
	 */
	private function getRandomString( $length = 10 ) {
		return substr( str_shuffle( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" ), 0, $length );
	}

	/**
	 * Helper method that returns a Title object
	 *
	 * @since 1.9
	 *
	 * @return Title
	 */
	private function getTitle(){
		return Title::newFromText( $this->getRandomString() );
	}

	/**
	 * Helper method that returns an User object
	 *
	 * @since 1.9
	 *
	 * @return User
	 */
	private function getUser() {
		return User::newFromName( $this->getRandomString() );
	}

	/**
	 * Helper method to create Title/ParserOutput object
	 * @see LinksUpdateTest::makeTitleAndParserOutput
	 *
	 * @since 1.9
	 *
	 * @param $titleName
	 * @param $id
	 *
	 * @return array
	 */
	private function makeTitleAndParserOutput() {
		$t = $this->getTitle();
		$t->resetArticleID( rand( 1, 1000 ) );

		$po = new ParserOutput();
		$po->setTitleText( $t->getPrefixedText() );

		return array( $t, $po );
	}

	/**
	 * Helper method to create Parser object
	 *
	 * @since 1.9
	 *
	 * @param $titleName
	 *
	 * @return Parser
	 */
	private function getParser() {
		global $wgContLang, $wgParserConf;

		$title = $this->getTitle();
		$user = $this->getUser();
		$wikiPage = new WikiPage( $title );
		$parserOptions = $wikiPage->makeParserOptions( $user );

		$parser = new Parser( $wgParserConf );
		$parser->setTitle( $title );
		$parser->setUser( $user );
		$parser->Options( $parserOptions );
		$parser->clearState();
		return $parser;
	}

	/**
	 * @test SMWHooks::onArticleFromTitle
	 *
	 * @since 1.9
	 */
	public function testOnArticleFromTitle() {
		$title = Title::newFromText( 'Property', SMW_NS_PROPERTY );
		$wikiPage = new WikiPage( $title );

		$result = SMWHooks::onArticleFromTitle( $title, $wikiPage );
		$this->assertTrue( $result );

		$title = Title::newFromText( 'Concepts', SMW_NS_CONCEPT );
		$wikiPage = new WikiPage( $title );

		$result = SMWHooks::onArticleFromTitle( $title, $wikiPage );
		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onParserFirstCallInit
	 *
	 * @since 1.9
	 */
	public function testOnParserFirstCallInit() {
		$parser = $this->getParser();
		$result = SMWHooks::onParserFirstCallInit( $parser );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onSpecialStatsAddExtra
	 *
	 * @since 1.9
	 */
	public function testOnSpecialStatsAddExtra() {
		$extraStats = array();
		$result = SMWHooks::onSpecialStatsAddExtra( $extraStats );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onParserAfterTidy
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $text
	 */
	public function testOnParserAfterTidy( $text ) {
		$parser = $this->getParser();
		$result = SMWHooks::onParserAfterTidy(
			$parser,
			$text
		);

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onLinksUpdateConstructed
	 *
	 * @since 1.9
	 */
	public function testOnLinksUpdateConstructed() {
		list( $title, $parserOutput ) = $this->makeTitleAndParserOutput();
		$update = new LinksUpdate( $title, $parserOutput );
		$result = SMWHooks::onLinksUpdateConstructed( $update );

		$this->assertTrue( $result );
	}

	/**
	 * @test SMWHooks::onArticleDelete
	 *
	 * @since 1.9
	 */
	public function testOnArticleDelete() {
		if ( method_exists( 'WikiPage', 'doEditContent' ) ) {

			$title = $this->getTitle();
			$user = $this->getUser();
			$wikiPage = new WikiPage(  $title );
			$revision = $wikiPage->getRevision();
			$reason = '';
			$error = '';

			$result = SMWHooks::onArticleDelete(
				$wikiPage,
				$user,
				$reason,
				$error
			);

			$this->assertTrue( $result );
		} else {
			$this->markTestSkipped(
				'Skipped test due to missing method (probably MW 1.19 or lower).'
			);
		}
	}

	/**
	 * @test SMWHooks::onNewRevisionFromEditComplete
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $text
	 */
	public function testOnNewRevisionFromEditComplete( $text ) {
		if ( method_exists( 'WikiPage', 'doEditContent' ) ) {

			$title = $this->getTitle();
			$user = $this->getUser();
			$wikiPage = new WikiPage(  $title );

			$content = \ContentHandler::makeContent(
				$text,
				$title,
				CONTENT_MODEL_WIKITEXT
			);

			$wikiPage->doEditContent( $content, "testing", EDIT_NEW );
			$this->assertTrue( $wikiPage->getId() > 0, "WikiPage should have new page id" );
			$revision = $wikiPage->getRevision();

			$result = SMWHooks::onNewRevisionFromEditComplete (
				$wikiPage,
				$revision,
				$wikiPage->getId(),
				$user
			);

			$this->assertTrue( $result );
		} else {
			$this->markTestSkipped(
				'Skipped test due to missing method (probably MW 1.19 or lower).'
			);
		}
	}

	/**
	 * @test SMWHooks::onSkinTemplateNavigation
	 *
	 * @since 1.9
	 */
	public function testOnSkinTemplateNavigation() {
		$skinTemplate = new \SkinTemplate();
		$skinTemplate->getContext()->setLanguage( \Language::factory( 'en' ) );
		$links = array();

		$result = SMWHooks::onSkinTemplateNavigation( $skinTemplate, $links );
		$this->assertTrue( $result );
	}


	/**
	 * @test SMWHooks::onResourceLoaderGetConfigVars
	 *
	 * @since 1.9
	 */
	public function testOnResourceLoaderGetConfigVars() {
		$vars = array();

		$result = SMWHooks::onResourceLoaderGetConfigVars( $vars );
		$this->assertTrue( $result );
	}

	/**
	 * Test SMWHooks::registerUnitTests
	 *
	 * Files are normally registered manually in registerUnitTests(). This test
	 * will compare registered files with the files available in the
	 * test directory.
	 *
	 * @since 1.9
	 */
	public function testRegisterUnitTests() {
		$registeredFiles = array();
		$result = SMWHooks::registerUnitTests( $registeredFiles );

		$this->assertTrue( $result );
		$this->assertNotEmpty( $registeredFiles );

		// Get all the *.php files
		// @see http://php.net/manual/en/class.recursivedirectoryiterator.php
		$testFiles = new \RegexIterator(
			new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( __DIR__ ) ),
			'/^.+\.php$/i',
			\RecursiveRegexIterator::GET_MATCH
		);

		// Array contains files that are excluded from verification because
		// those files do not contain any executable tests and therefore are
		// not registered (such as abstract classes, mock classes etc.)
		$excludedFiles = array(
			'dataitems/DataItem',
			'printers/ResultPrinter'
		);

		// Normalize excluded files
		foreach ( $excludedFiles as &$registeredFile ) {
			$registeredFile = $this->normalizePath( __DIR__ . '/' . $registeredFile . 'Test.php' );
		}

		// Normalize registered files
		foreach ( $registeredFiles as &$registeredFile ) {
			$registeredFile = $this->normalizePath( $registeredFile );
		}

		// Compare directory files with registered files
		foreach ( $testFiles as $fileName => $object ){
			$fileName = $this->normalizePath( $fileName );

			if ( !in_array( $fileName, $excludedFiles ) ) {
				$this->assertContains(
					$fileName,
					$registeredFiles,
					'Missing registration for ' . $fileName
				);
			}
		}
	}
}
