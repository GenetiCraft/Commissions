<?php
namespace FactionsPro\database;

use FactionsPro\FactionMain;
use FactionsPro\objects\Faction;
use FactionsPro\objects\Home;
use FactionsPro\objects\Plot;
use pocketmine\level\Level;

class FactionDatabase extends DataProvider {

    /** @var \SQLite3 $db */
    private $db;
    /** @var \SQLite3Stmt  */
    private $sqlGetFac, $sqlSaveFac, $sqlRemoveFac, $sqlGetFactionsByLeader;
    /** @var \SQLite3Stmt  */
    private $sqlGetPlot, $sqlSavePlot, $sqlRemovePlot, $sqlGetPlotByFaction;
    /** @var \SQLite3Stmt  */
    private $sqlGetHome, $sqlSaveHome, $sqlRemoveHome, $sqlGetHomeByFaction;

    /**
     * @param FactionMain $plugin
     * @param int $cacheSize
     */
    public function __construct(FactionMain $plugin, int $cacheSize = 0) {
        parent::__construct($plugin, $cacheSize);

        $this->db = new \SQLite3($this->plugin->getDataFolder()."factionsV2.db");
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS factions
            (id INTEGER PRIMARY KEY AUTOINCREMENT, faction TEXT, leader TEXT, desc TEXT, members TEXT, officers TEXT, power INTEGER, allies TEXT);
            CREATE TABLE IF NOT EXISTS homes
            (id INTEGER PRIMARY KEY AUTOINCREMENT, faction TEXT, x INTEGER, y INTEGER, z INTEGER, level TEXT);
            CREATE TABLE IF NOT EXISTS plots
            (id INTEGER PRIMARY KEY AUTOINCREMENT, faction TEXT, x1 INTEGER, z1 INTEGER, x2 INTEGER, z2 INTEGER, level TEXT);"
        );
        //Faction Table
        $this->sqlGetFac = $this->db->prepare(
            "SELECT faction, leader, members, officers, power, allies FROM factions WHERE faction = :faction;"
        );
        $this->sqlSaveFac = $this->db->prepare(
            "INSERT OR REPLACE INTO factions (id, faction, leader, members, officers, power, allies) VALUES
            ((select id from factions where faction = :faction OR leader = :leader),
             :faction, :leader, :members, :officers, :power, :allies);"
        );
        $this->sqlRemoveFac = $this->db->prepare(
            "DELETE FROM factions WHERE faction = :faction OR leader = :leader;"
        );
        $this->sqlGetFactionsByLeader = $this->db->prepare("SELECT * FROM factions WHERE leader = :leader;");
        //Plots Table
        $this->sqlGetPlot = $this->db->prepare("SELECT * FROM plots WHERE faction = :faction OR (( x1 = :x1 AND z1 = :z1 ) AND ( x2 = :x2 AND z2 =:z2 ));");
        $this->sqlSavePlot = $this->db->prepare("INSERT OR REPLACE INTO plots (faction, x1, z1, x2, z2, level) VALUES (:faction, :x1, :z1, :x2, :z2, :level);");
        $this->sqlRemovePlot = $this->db->prepare("DELETE FROM plots WHERE faction = :faction OR (( x1 = :x1 AND z1 = :z1 ) AND ( x2 = :x2 AND z2 =:z2 ));");
        $this->sqlGetPlotByFaction = $this->db->prepare("SELECT * FROM plots WHERE faction = :faction;");
        //Homes Table
        $this->sqlGetHome = $this->db->prepare("SELECT * FROM homes WHERE faction = :faction OR (x = :x AND y = :y AND z = :z AND level = :level);");
        $this->sqlSaveHome = $this->db->prepare("INSERT OR REPLACE INTO homes (faction, x, y, z, level) VALUES (:faction, :x, :y, :z, :level);");
        $this->sqlRemoveHome = $this->db->prepare("DELETE FROM homes WHERE faction = :faction OR (x = :x AND y = :y AND z = :z AND level = :level);");
        $this->sqlGetHomeByFaction = $this->db->prepare("SELECT * FROM homes WHERE faction = :faction;");
        $this->plugin->getLogger()->debug("SQLite data provider registered");
    }

    public function close() {
        $this->db->close();
        $this->plugin->getLogger()->debug("SQLite database closed!");
    }

    # FACTIONS

    /**
     * @param Faction $fac
     * @return bool
     */
    public function saveFac(Faction $fac) : bool {
        $members = implode(",",$fac->getMembers());
        $officers = implode(",",$fac->getOfficers());
        $allies = implode(",",$fac->getAllies());

        $this->sqlSaveFac->bindValue(":faction", $fac->getName());
        $this->sqlSaveFac->bindValue(":leader", $fac->getLeader());
        $this->sqlSaveFac->bindValue(":members", "{$members}");
        $this->sqlSaveFac->bindValue(":officers", "{$officers}");
        $this->sqlSaveFac->bindValue(":power", $fac->getPower());
        $this->sqlSaveFac->bindValue(":allies", "{$allies}");
        $this->sqlSaveFac->reset();
        $res = $this->sqlSaveFac->execute();
        if($res == false) {
            return false;
        }
        $this->cacheFac($fac);
        return true;
    }

    /**
     * @param Faction $fac
     * @return bool
     */
    public function deleteFac(Faction $fac) : bool {
        $this->sqlRemoveFac->bindValue(":faction", $fac->getName());
        $this->sqlRemoveFac->bindValue(":leader", $fac->getLeader());
        $this->sqlRemoveFac->reset();
        $res = $this->sqlRemoveFac->execute();
        if($res == false) {
            return false;
        }
        $this->cacheFac($fac);
        return true;
    }

    /**
     * @param string $factionName
     * @return Faction|null
     */
    public function getFac(string $factionName) {
        if (($fac = $this->getFacFromCache($factionName)) != null) {
            return $fac;
        }
        $this->sqlGetFac->bindValue(":faction", $factionName, SQLITE3_TEXT);
        $this->sqlGetFac->reset();
        $res = $this->sqlGetFac->execute();
        if($res != false) {
            $val = $res->fetchArray(SQLITE3_ASSOC);
            if ($val["members"] === null or $val["officers"] === "") {
                $officers = [];
            } else {
                $officers = explode(",", (string)$val["officers"]);
            }
            if ($val["members"] === null or $val["members"] === "") {
                $members = [];
            } else {
                $members = explode(",", (string)$val["members"]);
            }
            if ($val["allies"] === null or $val["allies"] === "") {
                $allies = [];
            } else {
                $allies = explode(",", (string)$val["allies"]);
            }
            $fac = new Faction($factionName, $val["leader"], (string)$val["desc"], $members, $officers, (int)$val["power"], $allies);
        } else {
            return null;
        }
        $this->sqlGetHomeByFaction->bindValue(":faction", $fac->getName());
        $this->sqlGetHomeByFaction->reset();
        $res = $this->sqlGetHomeByFaction->execute();
        if($res != false) {
            $val = $res->fetchArray(SQLITE3_ASSOC);
            $level = $this->plugin->getServer()->getLevelByName((string)$val["level"]);
            if($level instanceof Level) {
                $fac->setHome(New Home($fac->getName(), (int)$val["x"], (int)$val["y"], (int)$val["z"], $level));
            }
        }
        $this->sqlGetPlotByFaction->bindValue(":faction", $fac->getName());
        $this->sqlGetPlotByFaction->reset();
        $res = $this->sqlGetPlotByFaction->execute();
        if($res != false) {
            $val = $res->fetchArray(SQLITE3_ASSOC);
            $level = $this->plugin->getServer()->getLevelByName((string)$val["level"]);
            if($level instanceof Level) {
                $fac->setPlot(new Plot($fac->getName(), (int)$val["x1"], (int)$val["z1"], (int)$val["x2"], (int)$val["z2"], $level));
            }
        }
        $this->cacheFac($fac);
        return $fac;
    }

    /**
     * @return Faction[]
     */
    public function getAllFacs() : array {
        $factions = [];
        //TODO
        return $factions;
    }

    # PLOTS

    /**
     * @param int $x
     * @param int $z
     * @param string $level
     * @return Plot|null
     */
    public function getPlot(int $x, int $z, string $level) {
        //
    }
}