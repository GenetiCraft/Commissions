<?php
namespace jasonwynn10\CrowBar;

use pocketmine\block\BlockFactory;
use pocketmine\block\MonsterSpawner;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	public function onLoad() {
		BlockFactory::registerBlock(new class extends MonsterSpawner {
			public function getDrops(Item $item) : array {
				if($item->getId() === Item::WOODEN_HOE and stripos($item->getName(), "crowbar") !== false) {
					$spawnerTile = $this->getLevel()->getTile($this);
					if($spawnerTile !== null) { // check if there are any plugins which make spawners actually work
						return array_merge(parent::getDrops($item), [Item::get(Item::MOB_SPAWNER,0,1, $spawnerTile->getCleanedNBT() ?? "")]); // saves NBT to item
					}
					return array_merge(parent::getDrops($item), [Item::get(Item::MOB_SPAWNER)]);
				}
				return parent::getDrops($item);
			}
		}, true);
	}
}