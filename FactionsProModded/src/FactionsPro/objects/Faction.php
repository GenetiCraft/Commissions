<?php
namespace FactionsPro\objects;

use FactionsPro\FactionMain;
use pocketmine\Player;
use pocketmine\Server;

class Faction {

    CONST MEMBER = 0;
    CONST OFFICER = 1;
    CONST LEADER = 3;

    /** @var string $name */
    private $name;
    /** @var string[]  */
    private $members, $officers, $allies;
    /** @var string $leader */
    private $leader;
    /** @var string $desc */
    private $desc;
    /** @var int $power */
    private $power;
    /** @var Home $home*/
    private $home;
    /** @var Plot $plot */
    private $plot;

    public function __construct(string $name, string $leader, string $desc = "", array $members = [], array $officers = [], int $power = null, array $allies = [], Home $home = null, Plot $plot = null) {
        $this->name = $name;
        $this->desc = $desc;
        $this->members = $members;
        $this->officers = $officers;
        $this->allies = $allies;
        $this->leader = $leader;
        $this->home = $home;
        $this->plot = $plot;
        $this->power = $power == null ? $power : FactionMain::getInstance()->prefs->get("TheDefaultPowerEveryFactionStartsWith", 0);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPower() {
        return $this->power;
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDescription(string $desc) {
        $this->desc = $desc;
    }

    #HOME

    /**
     * @return Home|null
     */
    public function getHome() {
        return $this->home;
    }

    /**
     * @param Home $home
     */
    public function setHome(Home $home) {
        $this->home = $home;
    }

    #PLOT

    /**
     * @return Plot|null
     */
    public function getPlot() {
        return $this->plot;
    }

    /**
     * @param Plot $plot
     */
    public function setPlot(Plot $plot) {
        $this->plot = $plot;
    }

    public function removePlot() {
        $this->plot = null;
    }

    #MEMBERS

    /**
     * @return string[]
     */
    public function getAllMembers() : array {
        $arr = array_merge($this->members, $this->officers);
        $arr[] = $this->leader;
        return $arr;
    }

    /**
     * @return Player[]
     */
    public function getAllOnlineMembers() : array {
        $arr = array_merge($this->members,$this->officers);
        $arr[] = $this->leader;
        $online = [];
        foreach(Server::getInstance()->getOnlinePlayers() as $player) {
            foreach ($arr as $name) {
                if(strtolower($player->getName()) == strtolower($name)) {
                    $online[] = $player;
                }
            }
        }
        return $online;
    }

    /**
     * @return string[]|null
     */
    public function getMembers() {
        return $this->members;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isMember(string $player) : bool {
        return array_search($player, $this->getAllMembers()) == false ? false : true;
    }

    /**
     * @return string[]|null
     */
    public function getOfficers() {
        return $this->officers;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isOfficer(string $player) : bool {
        return array_search($player, $this->getAllMembers()) == false ? false : true;
    }

    /**
     * @param string $player
     * @param int $rank
     * @return bool
     */
    public function promote(string $player, int $rank) : bool {
        if($rank == self::OFFICER) {
            $key = array_search($player, $this->members);
            unset($this->members[$key]);
            $this->officers[] = $player;
            if(array_search($player, $this->officers) != false) {
                return true;
            }
        }elseif($rank == self::LEADER) {
            $key = array_search($player, $this->officers);
            if($key != false) {
                unset($this->officers[$key]);
                $this->leader = $player;
            }else{
                $key = array_search($player, $this->members);
                if($key != false) {
                    unset($this->members[$key]);
                    $this->leader = $player;
                }
            }
            if($this->getLeader() == $player) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $player
     * @param int $rank
     * @return bool
     */
    public function demote(string $player, int $rank) : bool {
        if($rank == self::MEMBER) {
            $key = array_search($player, $this->officers);
            unset($this->officers[$key]);
            $this->members[] = $player;
            if(array_search($player, $this->members) != false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isRanked(string $player) : bool {
        if(array_search($player, $this->officers) != false and $this->leader == $player) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getLeader() : string {
        return $this->leader;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isPlayerInFac(string $player) : bool {
        if(array_search($player, $this->getAllMembers()) != false) {
            return true;
        }
        return false;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function kickPlayer(string $player) : bool {
        $key = array_search($player, $this->members);
        if($key == false) {
            unset($this->members[$key]);
        }else{
            $key = array_search($player, $this->officers);
            if($key == false) {
                unset($this->officers[$key]);
            }
        }
        if(array_search($player, $this->getAllMembers()) == false) {
            return true;
        }
        return false;
    }

    #ALLIES

    /**
     * @return string[]|null
     */
    public function getAllies() {
        return $this->allies;
    }

    /**
     * @param Faction $ally
     * @return bool
     */
    public function addAlly(Faction $ally) : bool {
        $this->allies[] = $ally->getName();
        if(array_search($ally->getName(), $this->allies) != false) {
            return true;
        }
        return false;
    }

    /**
     * @param Faction $ally
     * @return bool
     */
    public function removeAlly(Faction $ally) : bool {
        $key = array_search($ally->getName(), $this->allies);
        unset($this->allies[$key]);
        if(array_search($ally->getName(), $this->allies) != false) {
            return false;
        }
        return true;
    }

    #OTHER

    public function __toString() {
        // TODO: Implement __toString() method.
        return "";
    }
}