<?php
namespace FactionsPro\objects;

class War {
    /** @var Faction[] $fac1 */
    public $facs;
    public function __construct(Faction ...$facs) {
        $this->facs = $facs;
    }
    public function getFactions() {
        return $this->facs;
    }
}