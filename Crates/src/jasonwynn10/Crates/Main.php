<?php
namespace jasonwynn10\Crates;

use pocketmine\block\Chest;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
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
				"wool:4 3",
				"tripwire_hook 3 {display:{Name:\"Psycho\"}}",
				"tripwire_hook 4 {display:{Name:\"Paranormal\"}}",
				"tripwire_hook 2 {display:{Name:\"Reaper\"}}",
				"tripwire_hook 1 {display:{Name:\"DemonLord\"}}",
				"wool:5 7 {display:{Name:\"mystical wool\"}}",
				"4:1 2"
			],
			"Paranormal" => ["stick 1 {ench:[{id:12s,lvl:10s}]}"],
			"Psycho" => ["stick 1 {display:{Name:\"§eTHE POWER\"},ench:[{id:12s,lvl:50s}]}"],
			"Reaper" => ["stick 1 {display:{Name:\"§eTHE POWER\"},ench:[{id:12s,lvl:50s}]}"],
			"DemonLord" => ["stick 1 {display:{Name:\"§eTHE POWER\"},ench:[{id:12s,lvl:50s}]}"]
		]);
		$this->getConfig()->reload();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param string $crate
	 *
	 * @return Item[]
	 */
	public function getRandomItems(string $crate) : array {
		$items = [];
		foreach($this->getConfig()->get($crate, []) as $itemString) {
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
					continue;
				}
				$item->setNamedTag($tags);
			}
			$items[] = $item;
		}
		$randomised[10] = $items[array_rand($items)]; // randomize item given
		return $randomised;
	}
	public function onPlace(BlockPlaceEvent $event) {
		if($event->getBlock() instanceof Chest) {
			$this->getServer()->getScheduler()->scheduleDelayedTask(new class($this, $event->getBlock()->asPosition()) extends PluginTask{
				/** @var Position $coords */
				private $coords;
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
	public function onInventoryOpen(InventoryOpenEvent $ev) {
		$chestTile = $ev->getInventory()->getHolder();
		if($chestTile instanceof \pocketmine\tile\Chest and in_array($chestTile->getName(), $this->getConfig()->getAll(true))) {
			$inventory = $ev->getPlayer()->getInventory();
			$item = $inventory->getItemInHand();
			if($item->getId() === Item::TRIPWIRE_HOOK and $item->getName() === $chestTile->getName()) {
				$chest = $chestTile->getInventory();
				if(count($chest->getViewers()) > 0) {
					$ev->setCancelled();
					$ev->getPlayer()->sendMessage("This Crate is already in use!");
					return;
				}
				$items = $this->getRandomItems($chestTile->getName());
				$ev->getInventory()->setContents($items);
				$item->pop();
				$inventory->setItem($inventory->getHeldItemIndex(), $item);
			}else{
				$ev->setCancelled();
			}
		}
	}
}