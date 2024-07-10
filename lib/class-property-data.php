<?php

namespace WP_Parser;

final readonly class PropertyData extends DTO {
	/**
	 * @param array<int, mixed> $tags
	 */
	public function __construct(
		public string $name,
		public int $line,
		public int $end_line,
		public ?string $default,
		public bool $static,
		public bool $visibility,
		public DocBlockData $doc,
	) {}
}
