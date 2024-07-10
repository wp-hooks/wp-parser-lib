<?php

namespace WP_Parser;

final readonly class DocBlockTagData extends DTO {
	public function __construct(
		public string $name,
		public ?string $content,
		public ?array $types,
		public ?string $link,
		public ?string $variable,
		public ?string $refers,
		public ?string $description,
	) {}
}
