<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use _64FF00\PureChat\PureChat;
use _64FF00\PurePerms\PurePerms;
use jasonwynn10\CR\command\KingdomCommand;
use jasonwynn10\CR\command\VoteCommand;
use jasonwynn10\CR\command\WarpMeCommand;
use jasonwynn10\CR\provider\KingdomProvider;
use jasonwynn10\CR\provider\SQLite3Provider;
use onebone\economyapi\EconomyAPI;
use pocketmine\entity\Effect;
use pocketmine\IPlayer;
use pocketmine\item\Item;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
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
	/** @var Config $voteRanks */
	private $voteRanks;

	/**
	 * @return Main
	 */
	public static function getInstance() : self {
		return self::$instance;
	}

	/**
	 * @return PurePerms|null|\pocketmine\plugin\Plugin
	 */
	public static function getPurePerms() : PurePerms {
		return self::getInstance()->getServer()->getPluginManager()->getPlugin("PurePerms");
	}

	public function onEnable() : void {
		$this->saveDefaultConfig();
		self::$instance = $this;
		$purePerms = self::getPurePerms();
		/** @var PureChat $pureChat */
		$pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
		$this->players = new Config($this->getDataFolder()."players.yml",Config::YAML);
		foreach($this->getKingdomNames() as $kingdom) {
			EconomyAPI::getInstance()->createAccount($kingdom."Kingdom", 1000.00,true);
		}
		$this->moneyRequestQueue = new Config($this->getDataFolder()."MoneyRequests.json", Config::JSON);
		$this->kingdomProvider = new SQLite3Provider($this); //TODO: more providers with config option
		$this->saveResource("VoteConfig.yml");
		$voteRanks = $this->voteRanks = new Config($this->getDataFolder()."VoteConfig.yml", Config::YAML);
		foreach($voteRanks->get("Ranks", []) as $rank => $data) {
			$purePerms->addGroup($rank);
			$group = $purePerms->getGroup($rank);
			$chat = str_replace("{rank}", $rank, $data["Chat"]);
			$pureChat->setOriginalChatFormat($group, $chat);
		}
		new EventListener($this);
		$this->getServer()->getCommandMap()->registerAll("cr",[
			new KingdomCommand($this),
			new VoteCommand($this),
			new WarpMeCommand($this)
		]);
		//TODO: power areas
		//TODO: Custom Enchantments
		//TODO: Crates
		//TODO: Envoys??
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
	 * @return float[]
	 */
	public function getMoneyRequestsInQueue() : array {
		return $this->moneyRequestQueue->getAll();
	}

	/**
	 * @return string[][][]
	 */
	public function getVoteRanks() : array {
		return $this->voteRanks->get("Ranks", []);
	}

	/**
	 * @param string $rank
	 *
	 * @return string[]
	 */
	public function getVoteRankEffects(string $rank) : array {
		return $this->voteRanks->getNested("Ranks.".$rank.".Effects",[]);
	}

	/**
	 * @param string $rank
	 *
	 * @return string[]
	 */
	public function getVoteRankItems(string $rank) : array {
		return $this->voteRanks->getNested("Ranks.".$rank.".Items", []);
	}

	/**
	 * @param Player $player
	 * @param string $rank
	 */
	public static function givePlayerRank(Player $player, string $rank) {
		$purePerms = self::getPurePerms();
		$main = self::getInstance();
		$purePerms->setGroup($player, $purePerms->getGroup($rank), null, time() + ($main->voteRanks->get("Rank-Timeout", 24) * 60 * 60));
		$items = $main->getVoteRankItems($rank);
		$effects = $main->getVoteRankEffects($rank);
		foreach($items as $itemString) {
			$player->getInventory()->addItem(self::getItemFromString($itemString));
		}
		foreach($effects as $effectString) {
			$player->addEffect(self::getEffectFromString($effectString));
		}
	}

	/**
	 * @param int $integer
	 *
	 * @return string
	 */
	public static function getRomanNumber(int $integer) : string {
		$characters = [
			'M' => 1000,
			'CM' => 900,
			'D' => 500,
			'CD' => 400,
			'C' => 100,
			'XC' => 90,
			'L' => 50,
			'XL' => 40,
			'X' => 10,
			'IX' => 9,
			'V' => 5,
			'IV' => 4,
			'I' => 1
		];
		$romanString = "";
		while ($integer > 0) {
			foreach ($characters as $rom => $arb) {
				if ($integer >= $arb) {
					$integer -= $arb;
					$romanString .= $rom;
					break;
				}
			}
		}
		return $romanString;
	}

	/**
	 * @param string $itemString
	 *
	 * @return Item
	 */
	public static function getItemFromString(string $itemString) : Item {
		$arr = explode(" ", $itemString);
		$item = Item::fromString($arr[0]);
		if(!isset($arr[1])) {
			$item->setCount($item->getMaxStackSize());
		}else{
			$item->setCount((int) $arr[1]);
		}
		if(isset($arr[2])) {
			$tags = $exception = null;
			$data = implode(" ", array_slice($arr, 2));
			try{
				$tags = JsonNBTParser::parseJSON($data);
			}catch(\Throwable $ex) {
				$exception = $ex;
			}
			if(!$tags instanceof CompoundTag or $exception !== null) {
				self::getInstance()->getLogger()->error("Invalid NBT tag!");
			}
			$item->setNamedTag($tags);
		}
		return $item;
	}

	/**
	 * @param string $effectString
	 *
	 * @return Effect
	 */
	public static function getEffectFromString(string $effectString) : Effect {
		$arr = explode(" ", $effectString);
		$effect = Effect::getEffectByName($arr[0]);
		if($effect === null) {
			$effect = Effect::getEffect((int) $arr[0]);
		}
		$amplification = 0;
		if(count($arr) >= 2) {
			$duration = ((int) $arr[1]) * 20; //ticks
		}else{
			$duration = $effect->getDefaultDuration();
		}

		if(count($arr) >= 3) {
			$amplification = (int) $arr[2];
			if($amplification > 255) {
				$amplification = 255;
			}elseif($amplification < 0) {
				$amplification = 0;
			}
		}

		if(count($arr) >= 4) {
			$v = strtolower($arr[3]);
			if($v === "on" or $v === "true" or $v === "t" or $v === "1") {
				$effect->setVisible(false);
			}
		}
		$effect->setDuration($duration)->setAmplifier($amplification);
		return $effect;
	}
}