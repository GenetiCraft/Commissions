<?php
namespace jasonwynn10\Elevators;

use pocketmine\block\SignPost;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onSignCreation(SignChangeEvent $ev) {
		if(stripos($ev->getLine(0), "elevator") !== false) {
			if(stripos($ev->getLine(1), "up") !== false) {
				$ev->getPlayer()->sendMessage(TextFormat::YELLOW."Elevator Sign Created!");
			}elseif(stripos($ev->getLine(1), "down") !== false) {
				$ev->getPlayer()->sendMessage(TextFormat::YELLOW."Elevator Sign Created!");
			}
		}
	}

	public function onTap(PlayerInteractEvent $ev) {
		$block = $ev->getBlock();
		if($block instanceof SignPost) {
			/** @var Sign|null $tile */
			$tile = $block->getLevel()->getTile($block);
			if($tile !== null and stripos($tile->getLine(0), "elevator") !== false) {
				$ev->setCancelled();
				if(stripos($tile->getLine(1), "up") !== false) {
					for($i = $block->getY() + 1; $i <= Level::Y_MAX; $i++) {
						$up = $block->getLevel()->getBlock($block->asPosition()->setComponents($block->getX(), $i, $block->getZ()));
						if($up instanceof SignPost) {
							/** @var Sign|null $upTile */
							$upTile = $block->getLevel()->getTile($up);
							if($upTile !== null and stripos($upTile->getLine(0), "elevator") !== false) {
								$ev->getPlayer()->teleport($ev->getPlayer()->asPosition()->setComponents($up->getX(), $up->getY(), $up->getZ()));
								return;
							}
						}
					}
				}elseif(stripos($tile->getLine(1), "down") !== false) {
					for($i = $block->getY() - 1; $i >= 0; $i--) {
						$down = $block->getLevel()->getBlock($block->asPosition()->setComponents($block->getX(), $i, $block->getZ()));
						if($down instanceof SignPost) {
							/** @var Sign|null $downTile */
							$downTile = $block->getLevel()->getTile($down);
							if($downTile !== null and stripos($downTile->getLine(0), "elevator") !== false) {
								$ev->getPlayer()->teleport($ev->getPlayer()->asPosition()->setComponents($down->getX(), $down->getY(), $down->getZ()));
								return;
							}
						}
					}
				}else{
					$ev->getPlayer()->sendMessage(TextFormat::RED."This elevator sign is invalid!");
					return;
				}
				$this->getLogger()->error("No matching elevator sign found");
			}
		}
	}
}