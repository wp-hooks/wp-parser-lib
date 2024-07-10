<?php

namespace WP_Parser;

final readonly class ArgumentData extends DTO {
	/**
	 * @param array<int, mixed> $tags
	 */
	public function __construct(
		public string $name,
		public ?string $default,
		public string $type,
	) {}
}
