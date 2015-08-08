<?php

namespace SMW\SQLStore;

use IteratorAggregate;
use ArrayIterator;

/**
 * @license GNU GPL v2+
 * @since 2.3
 *
 * @author mwjames
 */
class CompositePropertyTableDiffIterator implements IteratorAggregate {

	/**
	 * @var array
	 */
	private $diff = array();

	/**
	 * @var Cache|null
	 */
	private $cache = null;

	/**
	 * @since 2.3
	 *
	 * @param array $diff
	 */
	public function __construct( array $diff = array() ) {
		$this->diff = $diff;
	}

	/**
	 * @since 2.3
	 *
	 * @param array $diffrecord
	 */
	public function addElementToDiffRecord( array $diffrecord ) {
		$this->diff[] = $diffrecord;
	}

	/**
	 * @since 2.3
	 *
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator( $this->diff );
	}

	/**
	 * @since 2.3
	 *
	 * @param string|null $key
	 *
	 * @return array
	 */
	public function getUniqueFlatIdListFor( $key = null ) {

		$list = array();

		foreach ( $this as $diff ) {
			foreach ( $diff as $k => $value ) {

				if ( $k !== $key && $key !== null ) {
					continue;
				}

				foreach ( $value as $val ) {
					foreach ( $val as $element ) {

						if ( isset( $element['p_id'] ) ) {
							$list[] = $element['p_id'];
						}

						if ( isset( $element['s_id'] ) ) {
							$list[] = $element['s_id'];
						}

						if ( isset( $element['o_id'] ) ) {
							$list[] = $element['o_id'];
						}
					}
				}
			}
		}

		return array_unique( $list );
	}

}
