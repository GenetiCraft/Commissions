<?php
namespace FactionsPro\objects;

use pocketmine\level\Level;
use pocketmine\level\Position;

class Home extends Position {
    private $faction;
    public function __construct(string $faction,$x = 0, $y = 0, $z = 0, Level $level = null) {
        parent::__construct($x, $y, $z, $level);
        $this->faction = $faction;
    }

    /**
     * @return string
     */
    public function getFaction() {
        return $this->faction;
    }
}