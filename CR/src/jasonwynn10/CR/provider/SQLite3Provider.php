<?php
declare(strict_types=1);
namespace jasonwynn10\CR\provider;

use jasonwynn10\CR\Main;
use pocketmine\IPlayer;

class SQLite3Provider extends KingdomProvider {
	/** @var \SQLite3 $db */
	private $db;
	/** @var \SQLite3Stmt  */
	private $sqlGetKingdom, $sqlSetKingdomLeader, $sqlSetKingdomPower, $sqlSetKingdomMoney, $sqlSetKingdomMembers;

	/**
	 * SQLite3Provider constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		parent::__construct($plugin);
		$db = $this->db = new \SQLite3($plugin->getDataFolder()."kingdoms.db");
		$db->exec("CREATE TABLE IF NOT EXISTS kingdoms (kingdom TEXT PRIMARY KEY, leader TEXT, power INTEGER, money FLOAT, members TEXT);");
		$this->sqlGetKingdom = $db->prepare("SELECT * FROM kingdoms WHERE kingdom = :kingdom;");
		$this->sqlSetKingdomLeader = $db->prepare("UPDATE kingdoms SET leader = :leader WHERE kingdom = :kingdom;");
		$this->sqlSetKingdomPower = $db->prepare("UPDATE kingdoms SET power = :power WHERE kingdom = :kingdom;");
		$this->sqlSetKingdomMoney = $db->prepare("UPDATE kingdoms SET money = :money WHERE kingdom = :kingdom;");
		$this->sqlSetKingdomMembers = $db->prepare("UPDATE kingdoms SET memebrs = :members WHERE kingdom = :kingdom;");
	}

	/**
	 * @param string $kingdom
	 *
	 * @return string
	 */
	public function getKingdomLeader(string $kingdom) : string {
		$this->sqlGetKingdom->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$result = $this->sqlGetKingdom->execute();
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $result !== false ? $result->fetchArray(SQLITE3_ASSOC)["leader"] : "";
	}

	/**
	 * @param string  $kingdom
	 * @param IPlayer $player
	 *
	 * @return bool
	 */
	public function setKingdomLeader(string $kingdom, IPlayer $player) : bool{
		$this->sqlSetKingdomLeader->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$this->sqlSetKingdomLeader->bindValue(":leader", $player->getName(), SQLITE3_TEXT);
		return $this->sqlSetKingdomLeader->execute() !== false;
	}

	/**
	 * @param string $kingdom
	 *
	 * @return int
	 */
	public function getKingdomPower(string $kingdom) : int{
		$this->sqlGetKingdom->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$result = $this->sqlGetKingdom->execute();
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $result ? $result->fetchArray(SQLITE3_ASSOC)["power"] : 0;
	}

	/**
	 * @param string $kingdom
	 * @param int    $power
	 *
	 * @return bool
	 */
	public function setKingdomPower(string $kingdom, int $power) : bool {
		$this->sqlSetKingdomPower->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$this->sqlSetKingdomPower->bindValue(":power", $power < 0 ? 0 : $power, SQLITE3_INTEGER);
		return $this->sqlSetKingdomPower->execute() !== false;
	}

	/**
	 * @param string $kingdom
	 *
	 * @return float
	 */
	public function getKingdomMoney(string $kingdom) : float {
		$this->sqlGetKingdom->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$result = $this->sqlGetKingdom->execute();
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $result !== false ? $result->fetchArray(SQLITE3_ASSOC)["money"] : 0.00;
	}

	/**
	 * @param string $kingdom
	 * @param float  $money
	 *
	 * @return bool
	 */
	public function setKingdomMoney(string $kingdom, float $money) : bool {
		$this->sqlSetKingdomMoney->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$this->sqlSetKingdomMoney->bindValue(":power", $money < 0 ? 0.00 : $money, SQLITE3_FLOAT);
		return $this->sqlSetKingdomMoney->execute() !== false;
	}

	/**
	 * @param string $kingdom
	 *
	 * @return array
	 */
	public function getKingdomMembers(string $kingdom) : array {
		$this->sqlGetKingdom->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$result = $this->sqlGetKingdom->execute();
		if($result !== false) {
			return explode(",", $result->fetchArray(SQLITE3_ASSOC)["members"]);
		}
		return [];
	}

	/**
	 * @param string $kingdom
	 * @param array  $members
	 *
	 * @return bool
	 */
	public function setKingdomMembers(string $kingdom, array $members) : bool {
		$this->sqlSetKingdomMembers->bindValue(":kingdom", $kingdom, SQLITE3_TEXT);
		$this->sqlSetKingdomMembers->bindValue(":members", implode(",", $members), SQLITE3_TEXT);
		return $this->sqlSetKingdomMembers->execute() !== false;
	}
}