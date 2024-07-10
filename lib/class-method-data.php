<?php

namespace WP_Parser;

final readonly class MethodData extends DTO {
	/**
	 * @param array<int, mixed> $tags
	 */
	public function __construct(
		public string $name,
		public string $namespace,
		public int $line,
		public int $end_line,
		public bool $final,
		public bool $abstract,
		public bool $static,
		public string $visibility,
		public ArgumentDataList $arguments,
		public DocBlockData $doc,
	) {}
}
