<?php
namespace jasonwynn10\Elevators;

use pocketmine\block\SignPost;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;

class Main extends PluginBase implements Listener {
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onSignCreation(SignChangeEvent $ev) {
		if(stripos($ev->getLine(0), "elevator") !== false) {
			if(stripos($ev->getLine(1), "up") !== false) {
				$ev->getPlayer()->sendMessage("Elevator Sign Created!");
			}elseif(stripos($ev->getLine(1), "down") !== false) {
				$ev->getPlayer()->sendMessage("Elevator Sign Created!");
			}else{
				$ev->getPlayer()->sendMessage("Elevator Sign Invalid!");
			}
		}
	}

	public function onTap(PlayerInteractEvent $ev) {
		$block = $ev->getBlock();
		if($block instanceof SignPost) {
			/** @var Sign $tile */
			$tile = $block->getLevel()->getTile($block);
			if(stripos($tile->getLine(0), "elevator") !== false) {
				$ev->setCancelled();
				if(stripos($tile->getLine(1), "up") !== false) {
					for($i = $block->getY() + 1; $i <= Level::Y_MAX; $i++) {
						$up = $block->getLevel()->getBlock($block->asPosition()->setComponents($block->getX(), $i, $block->getZ()));
						if($up instanceof SignPost) {
							/** @var Sign $upTile */
							$upTile = $block->getLevel()->getTile($up);
							if(stripos($upTile->getLine(0), "elevator") !== false) {
								$ev->getPlayer()->teleport($ev->getPlayer()->asPosition()->setComponents($ev->getPlayer()->getX(), $i, $ev->getPlayer()->getZ()));
								return;
							}
						}
					}
				}elseif(stripos($tile->getLine(1), "down") !== false) {
					for($i = $block->getY() - 1; $i >= 0; $i--) {
						$down = $block->getLevel()->getBlock($block->asPosition()->setComponents($block->getX(), $i, $block->getZ()));
						if($down instanceof SignPost) {
							/** @var Sign $upTile */
							$upTile = $block->getLevel()->getTile($down);
							if(stripos($upTile->getLine(0), "elevator") !== false) {
								$ev->getPlayer()->teleport($ev->getPlayer()->asPosition()->setComponents($ev->getPlayer()->getX(), $i, $ev->getPlayer()->getZ()));
								return;
							}
						}
					}
				}
				$this->getLogger()->error("No matching elevator sign found");
			}
		}
	}
}