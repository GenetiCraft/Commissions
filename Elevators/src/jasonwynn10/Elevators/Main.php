<?php
namespace jasonwynn10\Elevators;

use pocketmine\block\SignPost;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;

class Main extends PluginBase implements Listener {
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onSign(SignChangeEvent $ev) {
		//TODO
	}
	public function onTap(PlayerInteractEvent $ev) {
		$block = $ev->getBlock();
		if($block instanceof SignPost) {
			/** @var Sign|null $tile */
			$tile = $block->getLevel()->getTile($block);
			if($tile !== null) {
				if(stripos($tile->getLine(0), "elevator") !== false)
					if(stripos($tile->getLine(1), "up") !== false) {
						//TODO
					}elseif(stripos($tile->getLine(1), "down") !== false) {
						//TODO
					}
			}
		}
	}
}