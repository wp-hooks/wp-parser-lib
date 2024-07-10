<?php

namespace WP_Parser;

final readonly class DocBlockData extends DTO {
	public function __construct(
		public string $description = '',
		public string $long_description = '',
		public ?DocBlockTagDataList $tags = null,
	) {}
}
