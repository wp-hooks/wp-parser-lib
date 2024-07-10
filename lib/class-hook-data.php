<?php

namespace WP_Parser;

final readonly class HookData extends DTO {
	public function __construct(
		public string $name,
		public int $line,
		public int $end_line,
		public string $type,
		public array $arguments,
		public DocBlockData $doc,
	) {}
}
