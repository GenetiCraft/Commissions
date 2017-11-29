<?php
declare(strict_types=1);
namespace jasonwynn10\CR\provider;

use jasonwynn10\CR\Main;

abstract class KingdomProvider {
	/**
	 * KingdomProvider constructor.
	 *
	 * @param Main $plugin
	 */
	abstract public function __construct(Main $plugin);

	/**
	 * @param string $kingdom
	 *
	 * @return string
	 */
	abstract public function getKingdomLeader(string $kingdom) : string;

	/**
	 * @param string $kingdom
	 *
	 * @return int
	 */
	abstract public function getKingdomPower(string $kingdom) : int;

	/**
	 * @param string $kingdom
	 * @param int    $power
	 *
	 * @return mixed
	 */
	abstract public function setKingdomPower(string $kingdom, int $power);

	/**
	 * @param string $kingdom
	 *
	 * @return float
	 */
	abstract public function getKingdomMoney(string $kingdom) : float;

	/**
	 * @param string $kingdom
	 *
	 * @return array
	 */
	abstract public function getKingdomMembers(string $kingdom) : array;
}