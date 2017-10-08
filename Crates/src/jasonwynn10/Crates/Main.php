<?php
namespace jasonwynn10\Crates;

use pocketmine\block\Chest;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
	public function onEnable() {
		@mkdir($this->getDataFolder());
		new Config($this->getDataFolder()."config.yml",Config::YAML, [
			"Spirit" => [
				"stick 2 {display:{Name:\"§r§6§lNormal stick\"}}",
				"diamond_sword 1",
				"wool:4 3"
			],
			"Paranormal" => ["stick 1 {ench:[{id:12s,lvl:10s}]}"],
			"Psycho" => ["stick 1 {display:{Name:\"§eTHE POWER\"},ench:[{id:12s,lvl:50s}]}"],
			"Reaper" => ["stick 1 {display:{Name:\"§eTHE POWER\"},ench:[{id:12s,lvl:50s}]}"],
			"DemonLord" => ["stick 1 {display:{Name:\"§eTHE POWER\"},ench:[{id:12s,lvl:50s}]}"]
		]);
		$this->getConfig()->reload();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onPlace(BlockPlaceEvent $event) {
		if($event->getBlock() instanceof Chest) {
			$this->getServer()->getScheduler()->scheduleDelayedTask(new class($this, $event->getBlock()->asPosition()) extends PluginTask{
				public function __construct(Plugin $owner, Position $coords) {
					parent::__construct($owner);
					$this->coords = $coords;
				}
				public function onRun(int $currentTick) {
					/** @var \pocketmine\tile\Chest|null $tile */
					$tile = $this->coords->getLevel()->getTile($this->coords);
					if(in_array($tile->getName(), $this->getOwner()->getConfig()->getAll(true))) {
						$tile->namedtag->Lock = new StringTag("Lock", $tile->getName());
					}
				}
			}, 1); // 1 tick delay
		}
	}
	public function onTap(PlayerInteractEvent $ev) {
		$block = $ev->getBlock();
		$hand = $ev->getItem();
		if($block instanceof Chest) {
			/** @var \pocketmine\tile\Chest|null $chestTile */
			$chestTile = $block->getLevel()->getTile($block);
			if($chestTile !== null and in_array($chestTile->getName(), $this->getConfig()->getAll(true))) {
				if($hand->getId() !== Item::TRIPWIRE_HOOK) {
					$chestTile->getInventory()->clearAll();
					$ev->setCancelled();
					return;
				}
				$items = [];
				foreach($this->getConfig()->get($chestTile->getName(), []) as $itemString) {
					$arr = explode(" ", $itemString);
					$item = Item::fromString($arr[0]);
					if(!isset($arr[1])) {
						$item->setCount($item->getMaxStackSize());
					}else{
						$item->setCount((int) $arr[1]);
					}
					if(isset($arr[2])) {
						$tags = $exception = null;
						$data = implode(" ", array_slice($arr, 2));
						try{
							$tags = JsonNBTParser::parseJSON($data);
						}catch(\Throwable $ex) {
							$exception = $ex;
						}
						if(!$tags instanceof CompoundTag or $exception !== null) {
							$this->getLogger()->error("Invalid NBT tag!");
							return;
						}
						$item->setNamedTag($tags);
					}
					$items[] = $item;
				}
				$randomised = [];
				foreach(array_rand($items, count($items) - 1) as $key) { // randomize items
					$slot = mt_rand(0, $chestTile->getInventory()->getSize());
					$randomised[$slot] = $items[$key]; // randomize slots they are located
				}
				$chestTile->getInventory()->setContents($randomised, true);
			}
		}
	}
	public function onInventoryOpen(InventoryOpenEvent $ev) {
		$chestTile = $ev->getInventory()->getHolder();
		if($chestTile instanceof \pocketmine\tile\Chest and in_array($chestTile->getName(), $this->getConfig()->getAll(true))) {
			$inventory = $ev->getPlayer()->getInventory();
			$i = 0;
			foreach($inventory->getContents() as $item) {
				if($item->getId() === Item::TRIPWIRE_HOOK and $item->getName() === $chestTile->getName()) {
					$inventory->setItem($i, $item->pop(), true);
					return;
				}
			}
		}
	}
}