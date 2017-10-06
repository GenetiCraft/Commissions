<?php
namespace FactionsPro\objects;

use pocketmine\level\Level;
use pocketmine\level\Position;

class Plot {
    /** @var int  */
    private $x1, $z1, $x2, $z2;
    /** @var Level $level */
    private $level;
    /** @var string $faction */
    private $faction;

    public function __construct(string $faction,int $x1, int $z1, int $x2, int $z2, Level $level) {
        if($x1 > $x2) {
            $this->x1 = $x1;
            $this->x2 = $x2;
        }else{
            $this->x1 = $x2;
            $this->x2 = $x1;
        }
        if($z1 > $z2) {
            $this->z1 = $z1;
            $this->z1 = $z2;
        }else{
            $this->z1 = $z2;
            $this->z2 = $z1;
        }
        $this->level = $level;
        $this->faction = $faction;
    }

    /**
     * @param Position $pos
     * @return bool
     */
    public function isInPlot(Position $pos) {
        if($pos->getX() <= $this->x1 and $pos->getX() >= $this->x2
            and $pos->getZ() <= $this->z1 and $pos->getZ() >= $this->z2
            and $pos->getLevel() == $this->level) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFaction() {
        return $this->faction;
    }
}