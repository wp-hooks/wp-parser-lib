<?php
namespace WP_Parser;

abstract readonly class DTO implements \ArrayAccess {
	public function toArray() : array {
		$vars = get_object_vars( $this );
		$vars = array_filter( $vars, fn( $value ) => ( null !== $value ) );

		foreach ( $vars as &$value ) {
			if ( $value instanceof DTO ) {
				$value = $value->toArray();
			}
		}

		return $vars;
	}

	public function offsetExists( $offset ) : bool {
		return isset( $this->$offset );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->$offset;
	}

	public function offsetSet( $offset, $value ) : void {
		$this->$offset = $value;
	}

	public function offsetUnset( $offset ) : void {
		unset( $this->$offset );
	}
}
