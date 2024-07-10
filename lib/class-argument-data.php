<?php

namespace WP_Parser;

final readonly class ArgumentData extends DTO {
	public function __construct(
		public string $name,
		public ?string $default,
		public string $type,
	) {}
}
