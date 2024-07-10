<?php

namespace WP_Parser;

final readonly class ArgumentDataList extends DTOList {
	public function __construct( ArgumentData ...$data ) {
		parent::__construct( ...$data );
	}
}
