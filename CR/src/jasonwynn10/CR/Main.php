<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {
	private static $instance;
	/** @var Config $players */
	private $players;
	/** @var Config $moneyRequestQueue */
	private $moneyRequestQueue;

	/**
	 * @return Main
	 */
	public static function getInstance() : self {
		return self::$instance;
	}

	public function onEnable() : void {
		$this->saveDefaultConfig();
		self::$instance = $this;
		$this->players = new Config($this->getDataFolder()."players.yml",Config::YAML);
		$this->moneyRequestQueue = new Config($this->getDataFolder()."MoneyRequests.json", Config::JSON);
		new EventListener($this);
	}

	/**
	 * @return string[]
	 */
	public function getKingdomNames() : array {
		return array_keys($this->getConfig()->get("Kingdoms", []));
	}

	public function setPlayerKingdom(IPlayer $player, string $kingdom) : bool {
		$this->players->set($player->getName(), $kingdom);
		return $this->players->save(true);
	}

	/**
	 * @param IPlayer $player
	 *
	 * @return string|null
	 */
	public function getPlayerKingdom(IPlayer $player) : ?string {
		/** @noinspection PhpStrictTypeCheckingInspection */
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->players->get($player->getName(), null); // First kingdom is default
	}

	/**
	 * @param IPlayer $player
	 * @param float   $amount
	 *
	 * @return bool
	 */
	public function addMoneyRequestToQueue(IPlayer $player, float $amount) : bool {
		$this->moneyRequestQueue->set($player->getName(), $amount); //TODO: what if player submits form 2x when leader is offline?
		return $this->moneyRequestQueue->save(true);
	}
}