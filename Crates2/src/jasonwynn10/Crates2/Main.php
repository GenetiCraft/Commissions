<?php
declare(strict_types=1);
namespace jasonwynn10\Crates2;

use pocketmine\block\Chest;
use pocketmine\event\block\BlockPlaceEvent;
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
			"Vote" => [
				"enchanted_golden_apple 16",
				"diamond_helmet 1 {ench:[{id:0s,lvl:2s}]}",
				"diamond_chestplate 1 {ench:[{id:0s,lvl:2s}]}",
				"diamond_leggings 1 {ench:[{id:0s,lvl:2s}]}",
				"diamond_boots 1 {ench:[{id:0s,lvl:2s}]}",
				"ender_pearl 16",
				"bedrock 12"
			],
			"Legendary" => [
				"enchanted_golden_apple 64",
				"bedrock 32",
				"ender_pearl 16",
				"diamond_helmet 1 {ench:[{id:0s,lvl:4s}]}",
				"diamond_chestplate 1 {ench:[{id:0s,lvl:4s}]}",
				"diamond_leggings 1 {ench:[{id:0s,lvl:4s}]}",
				"diamond_boots 1 {ench:[{id:0s,lvl:4s}]}"
			],
			"Rare" => [
				"enchanted_golden_apples 64",
				"diamond_helmet 1 {ench:[{id:0s,lvl:5s}]}",
				"diamond_chestplate 1 {ench:[{id:0s,lvl:5s}]}",
				"diamond_leggings 1 {ench:[{id:0s,lvl:5s}]}",
				"diamond_boots 1 {ench:[{id:0s,lvl:5s}]}",
				"ender_pearl 16",
				"bedrock 64"
			]
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
		if($event->getBlock() instanceof Chest and in_array($event->getBlock()->getName(), $this->getConfig()->getAll(true))) {
			$this->getServer()->getScheduler()->scheduleDelayedTask(new class($this, $event->getBlock()->asPosition()) extends PluginTask {
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
	public function onInteract(PlayerInteractEvent $ev) {
		if($ev->getBlock() instanceof Chest and in_array($ev->getBlock()->getName(), $this->getConfig()->getAll(true))) {
			//TODO
		}
	}
}