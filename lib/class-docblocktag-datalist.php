<?php

namespace WP_Parser;

final readonly class DocBlockTagDataList extends DTOList {
	public function __construct( DocBlockTagData ...$tag ) {
		parent::__construct( ...$tag );
	}
}
