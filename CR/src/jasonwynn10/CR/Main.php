<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use jasonwynn10\CR\provider\KingdomProvider;
use jasonwynn10\CR\provider\SQLite3Provider;
use onebone\economyapi\EconomyAPI;
use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {
	private static $instance;
	/** @var Config $players */
	private $players;
	/** @var Config $moneyRequestQueue */
	private $moneyRequestQueue;
	/** @var KingdomProvider $kingdomProvider */
	private $kingdomProvider;

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
		foreach($this->getKingdomNames() as $kingdom) {
			EconomyAPI::getInstance()->createAccount($kingdom."Kingdom", 1000.00,true);
		}
		$this->moneyRequestQueue = new Config($this->getDataFolder()."MoneyRequests.json", Config::JSON);
		$this->kingdomProvider = new SQLite3Provider($this); //TODO: more providers with config option
		new EventListener($this);
	}

	/**
	 * @return string[]
	 */
	public function getKingdomNames() : array {
		return array_keys($this->getConfig()->get("Kingdoms", []));
	}

	/**
	 * @param IPlayer $player
	 * @param string  $kingdom
	 *
	 * @return bool
	 */
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
		return $this->players->get($player->getName(), null);
	}

	/**
	 * @param string $kingdom
	 *
	 * @return string
	 */
	public function getKingdomLeader(string $kingdom) : string {
		return $this->kingdomProvider->getKingdomLeader($kingdom);
	}

	/**
	 * @param string $kingdom
	 *
	 * @return int
	 */
	public function getKingdomPower(string $kingdom) : int {
		return $this->kingdomProvider->getKingdomPower($kingdom);
	}

	/**
	 * @param string $kingdom
	 *
	 * @return float
	 */
	public function getKingdomMoney(string $kingdom) : float {
		return $this->kingdomProvider->getKingdomMoney($kingdom);
	}

	/**
	 * @param string $kingdom
	 *
	 * @return string[]
	 */
	public function getKingdomMembers(string $kingdom) : array {
		return $this->kingdomProvider->getKingdomMembers($kingdom);
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

	/**
	 * @return array
	 */
	public function getMoneyRequestsInQueue() : array {
		return $this->moneyRequestQueue->getAll();
	}
}