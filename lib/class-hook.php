<?php

namespace WP_Parser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Location;

final readonly class Hook {
	/**
	 * @param array<int, string> $args
	 */
	public function __construct(
		private string $name,
		private ?DocBlock $docBlock,
		private string $type,
		private array $args,
		private Location $location,
		private Location $endLocation,
	) {}

	public function getName(): string {
		return $this->name;
	}

	public function getDocBlock(): ?DocBlock {
		return $this->docBlock;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getArgs(): array {
		return $this->args;
	}

	public function getLocation(): Location {
		return $this->location;
	}

	public function getEndLocation(): Location {
		return $this->endLocation;
	}
}
