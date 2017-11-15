<?php
declare(strict_types=1);
namespace jasonwynn10\Crates2;

use pocketmine\block\Chest;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

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
	 * @return Item
	 */
	public function getRandomItem(string $crate) : Item {
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
		return $items[array_rand($items)]; // randomize item given
	}

	/**
	 * @priority MONITOR
	 * @ignoreCancelled true
	 *
	 * @param BlockPlaceEvent $ev
	 */
	public function onPlace(BlockPlaceEvent $ev) {
		echo "placed event\n";
		if($ev->getBlock() instanceof Chest and in_array($ev->getBlock()->getName(), $this->getConfig()->getAll(true))) {
			$particle = new FloatingTextParticle($ev->getBlock()->add(0,1),TextFormat::GREEN.TextFormat::OBFUSCATED."kj".TextFormat::RESET." ".$ev->getBlock()->getName()." ".TextFormat::GREEN.TextFormat::OBFUSCATED."kj");
			$level = $ev->getBlock()->getLevel();
			$level->addParticle($particle);
			echo "particle added\n";
		}
	}

	/**
	 * @priority LOW
	 * @ignoreCancelled false
	 *
	 * @param PlayerInteractEvent $ev
	 */
	public function onInteract(PlayerInteractEvent $ev) {
		if($ev->getBlock() instanceof Chest and
			in_array($ev->getBlock()->getName(), $this->getConfig()->getAll(true)) and
			$ev->getItem()->getId() === Item::TRIPWIRE_HOOK and
			in_array($ev->getItem()->getName(), $this->getConfig()->getAll(true))
		) {
			$ev->setCancelled();
			$particles = new Config($this->getDataFolder()."particles.json", Config::JSON, [
				"Vote" => [
					0 => 255,
					1 => 255,
					2 => 0,
					3 => 0
				],
				"Legendary" => [
					0 => 255,
					1 => 0,
					2 => 128,
					3 => 255
				],
				"Rare" => [
					0 => 255,
					1 => 0,
					2 => 255,
					3 => 0
				]
			]);
			$argb = $particles->get($ev->getItem()->getName(), [
				0 => 255,
				1 => 0,
				2 => 255,
				3 => 255
			]); // default to light blue
			$particle = new DustParticle($ev->getBlock()->add(0, 1), $argb[1], $argb[2], $argb[3], $argb[0]);
			$level = $ev->getBlock()->getLevel();
			$level->addParticle($particle);
			$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this, $ev->getPlayer(), $ev->getBlock()->getName()) extends PluginTask {
				private $player;
				private $name;
				private $current = 0;
				public function __construct(Plugin $owner, Player $player, string $name) {
					parent::__construct($owner);
					$this->player = $player->getName();
					$this->name = $name;
				}
				public function onRun(int $currentTick) {
					echo "title task\n";
					$player = $this->getOwner()->getServer()->getPlayerExact($this->player);
					if($player === null) {
						echo "player offline\n";
						$this->getHandler()->remove();
						return;
					}
					$arr = $this->getOwner()->getConfig()->get($this->name, []);
					$rand = $arr[$r = array_rand($arr)];
					echo "random = ".$r."\n";
					if($this->current <= 3 * count($arr)) {
						$str = explode(" ", $rand);
						$player->addTitle(TextFormat::BLUE.TextFormat::OBFUSCATED."kj".TextFormat::RESET.TextFormat::BLUE." ".str_replace("_"," ", $str[0])." ".TextFormat::OBFUSCATED."kj", "", 0, 10, 0);
						echo "title added\n";
						$this->current++;
					}else{
						/** @var Item $item */
						/** @noinspection PhpUndefinedMethodInspection */
						$item = $this->getOwner()->getRandomItem($this->name);
						$player->addTitle(TextFormat::BLUE.TextFormat::OBFUSCATED."kj".TextFormat::RESET.TextFormat::BLUE." ".str_replace("_"," ", $item->getName())." ".TextFormat::OBFUSCATED."kj", "", 0, 2 * 20, 0);
						$player->getInventory()->addItem($item);
						echo "success\n";
						$this->getHandler()->remove();
					}
				}
			},5,10);
		}elseif($ev->getBlock() instanceof Chest and
			in_array($ev->getBlock()->getName(), $this->getConfig()->getAll(true)) and
			$ev->getItem()->getId() !== Item::TRIPWIRE_HOOK
		) {
			$ev->setCancelled();
		}
	}
}