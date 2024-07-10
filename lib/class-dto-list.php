<?php
namespace WP_Parser;

abstract readonly class DTOList extends DTO {
	/**
	 * @var array<int, DTO>
	 */
	private array $datalist;

	public function __construct( DTO ...$data ) {
		$this->datalist = $data;
	}

	public function toArray() : array {
		return array_map( fn( DTO $dto ) : array => ( $dto->toArray() ), $this->datalist );
	}
}
