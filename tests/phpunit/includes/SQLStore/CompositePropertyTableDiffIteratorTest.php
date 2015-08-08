<?php

namespace SMW\Tests\SQLStore;

use SMW\SQLStore\CompositePropertyTableDiffIterator;

/**
 * @covers \SMW\SQLStore\CompositePropertyTableDiffIterator
 * @group semantic-mediawiki
 *
 * @license GNU GPL v2+
 * @since 2.3
 *
 * @author mwjames
 */
class CompositePropertyTableDiffIteratorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\SQLStore\CompositePropertyTableDiffIterator',
			new CompositePropertyTableDiffIterator()
		);
	}

	/**
	 * @dataProvider diffDataProvider
	 */
	public function testFlatListOfIds( $list, $expectedInsert, $expectedDelete ) {

		$instance = new CompositePropertyTableDiffIterator(
			unserialize( $list )
		);

		$this->assertEquals(
			$expectedInsert,
			$instance->getUniqueFlatIdListFor( 'i' )
		);

		$this->assertEquals(
			$expectedDelete,
			$instance->getUniqueFlatIdListFor( 'd' )
		);
	}

	public function diffDataProvider() {

		$provider[] = array(
			'a:1:{i:0;a:2:{s:1:"i";a:0:{}s:1:"d";a:0:{}}}',
			array(),
			array()
		);

		// Insert
		$provider[] = array(
			'a:1:{i:0;a:2:{s:1:"i";a:2:{s:15:"smw_di_wikipage";a:1:{i:0;a:3:{s:4:"s_id";i:1706;s:4:"p_id";i:1707;s:4:"o_id";i:388;}}s:12:"smw_fpt_mdat";a:1:{i:0;a:3:{s:4:"s_id";i:1706;s:12:"o_serialized";s:19:"1/2015/8/8/18/51/39";s:9:"o_sortkey";s:15:"2457243.2858681";}}}s:1:"d";a:2:{s:15:"smw_di_wikipage";a:0:{}s:12:"smw_fpt_mdat";a:0:{}}}}',
			array(
				1707,
				1706,
				388
			),
			array()
		);

		// Insert/Delete
		$provider[] = array(
			'a:1:{i:0;a:2:{s:1:"i";a:2:{s:15:"smw_di_wikipage";a:1:{i:0;a:3:{s:4:"s_id";i:1706;s:4:"p_id";i:1707;s:4:"o_id";i:296;}}s:12:"smw_fpt_mdat";a:1:{i:0;a:3:{s:4:"s_id";i:1706;s:12:"o_serialized";s:18:"1/2015/8/8/19/2/39";s:9:"o_sortkey";s:15:"2457243.2935069";}}}s:1:"d";a:2:{s:15:"smw_di_wikipage";a:1:{i:0;a:3:{s:4:"s_id";i:1706;s:4:"p_id";i:1707;s:4:"o_id";i:388;}}s:12:"smw_fpt_mdat";a:1:{i:0;a:3:{s:4:"s_id";i:1706;s:12:"o_serialized";s:19:"1/2015/8/8/18/51/39";s:9:"o_sortkey";s:15:"2457243.2858681";}}}}}',
			array(
				1707,
				1706,
				296
			),
			array(
				1707,
				1706,
				388
			)
		);

		return $provider;
	}


}
