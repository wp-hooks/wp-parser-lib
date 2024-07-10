<?php

namespace WP_Parser;

final readonly class DocBlockTagDataList extends DTOList {
	public function __construct( DocBlockTagData ...$data ) {
		parent::__construct( ...$data );
	}
}
