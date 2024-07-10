<?php

namespace WP_Parser;

final readonly class MethodDataList extends DTOList {
	public function __construct( MethodData ...$data ) {
		parent::__construct( ...$data );
	}
}
