<?php

namespace WP_Parser;

final readonly class DocBlockData extends DTO {
	/**
	 * @param array<int, mixed> $tags
	 */
	public function __construct(
		public string $description = '',
		public string $long_description = '',
		public array $tags = [],
	) {}
}
