<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	public function onEnable() : void {
		$this->saveDefaultConfig();
		new EventListener($this);
	}

	/**
	 * @return string[]
	 */
	public function getKingdoms() : array {
		return $this->getConfig()->getAll(true);
	}
}