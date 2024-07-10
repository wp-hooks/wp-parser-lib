<?php

namespace WP_Parser;

final readonly class DocBlockTagsData extends DTOList {
	public function __construct( DocBlockTagData ...$tag ) {
		parent::__construct( ...$tag );
	}
}
