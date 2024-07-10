<?php

namespace WP_Parser;

final readonly class HookDataList extends DTOList {
	public function __construct( HookData ...$data ) {
		parent::__construct( ...$data );
	}
}
