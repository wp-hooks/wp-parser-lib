<?php

namespace WP_Parser;

final readonly class PropertyDataList extends DTOList {
	public function __construct( PropertyData ...$data ) {
		parent::__construct( ...$data );
	}
}
