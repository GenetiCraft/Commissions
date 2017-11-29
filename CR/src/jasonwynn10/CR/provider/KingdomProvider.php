<?php
declare(strict_types=1);
namespace jasonwynn10\CR\provider;

use jasonwynn10\CR\Main;
use pocketmine\IPlayer;

abstract class KingdomProvider {
	/** @var Main $plugin */
	protected $plugin;

	/**
	 * KingdomProvider constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @param string $kingdom
	 *
	 * @return string
	 */
	abstract public function getKingdomLeader(string $kingdom) : string;

	/**
	 * @param string  $kingdom
	 * @param IPlayer $player
	 */
	abstract public function setKingdomLeader(string $kingdom, IPlayer $player);

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
	 * @return bool
	 */
	abstract public function setKingdomPower(string $kingdom, int $power) : bool;

	/**
	 * @param string $kingdom
	 * @param int    $power
	 *
	 * @return bool
	 */
	public function addKingdomPower(string $kingdom, int $power) : bool {
		$current = $this->getKingdomPower($kingdom);
		return $this->setKingdomPower($kingdom, $current + abs($power));
	}

	/**
	 * @param string $kingdom
	 * @param int    $power
	 *
	 * @return bool
	 */
	public function removeKingdomPower(string $kingdom, int $power) : bool {
		$current = $this->getKingdomPower($kingdom);
		return $this->setKingdomPower($kingdom, $current - abs($power));
	}

	/**
	 * @param string $kingdom
	 *
	 * @return float
	 */
	abstract public function getKingdomMoney(string $kingdom) : float;

	/**
	 * @param string $kingdom
	 * @param float  $money
	 *
	 * @return bool
	 */
	abstract public function setKingdomMoney(string $kingdom, float $money) : bool;

	/**
	 * @param string $kingdom
	 * @param float  $money
	 *
	 * @return bool
	 */
	public function addKingdomMoney(string $kingdom, float $money) : bool {
		$current = $this->getKingdomMoney($kingdom);
		return $this->setKingdomMoney($kingdom, $current + abs($money));
	}

	/**
	 * @param string $kingdom
	 * @param float  $money
	 *
	 * @return bool
	 */
	public function removeKingdomMoney(string $kingdom, float $money) : bool {
		$current = $this->getKingdomMoney($kingdom);
		return $this->setKingdomMoney($kingdom, $current - abs($money));
	}
}