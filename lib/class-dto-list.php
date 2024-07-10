<?php
namespace WP_Parser;

abstract readonly class DTOList extends DTO {
	/**
	 * @var array<int, DTO>
	 */
	private array $datalist;

	public function __construct( DTO ...$dto ) {
		$this->datalist = $dto;
	}

	public function toArray() : array {
		return array_map( function( DTO $dto ) {
			return $dto->toArray();
		}, $this->datalist );
	}
}
