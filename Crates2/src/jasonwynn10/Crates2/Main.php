<?php
declare(strict_types=1);
namespace jasonwynn10\Crates2;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Chest;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	/** @var FloatingTextParticle[] $particles */
	private $particles = [];
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
		$particlesData = new Config($this->getDataFolder()."TextParticles.json", Config::JSON);
		foreach($particlesData->getAll() as $pos => $title) {
			$coords = explode(";", $pos);
			$this->particles[] = new FloatingTextParticle(new Vector3($coords[0], $coords[1], $coords[2]),"", $title);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable() {
		$particlesData = new Config($this->getDataFolder()."TextParticles.json", Config::JSON);
		$particles = [];
		foreach($this->particles as $particle) {
			$name = $particle->getTitle();
			$pos = $particle->x.";".$particle->y.";".$particle->z;
			$particles[$pos] = $name;
		}
		$particlesData->setAll($particles);
		$particlesData->save(true);
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
		if($ev->getBlock()->getId() === Block::CHEST or in_array($ev->getItem()->getName(), $this->getConfig()->getAll(true))) {
			$block = $ev->getBlock();
			$block->x <= 0 ? $pos = $block->add(0.5,1) : $pos = $block->add(-0.5,1);
			$block->z <= 0 ? $pos = $pos->add(0,0,0.5) : $pos = $pos->add(0,0,-0.5);
			$particle = new FloatingTextParticle($pos,"", TextFormat::GREEN.$ev->getItem()->getName());
			$block->getLevel()->addParticle($particle);
			$this->particles[] = $particle;
		}
	}

	/**
	 * @priority MONITOR
	 * @ignoreCancelled true
	 *
	 * @param BlockBreakEvent $ev
	 */
	public function onBreak(BlockBreakEvent $ev) {
		$block = $ev->getBlock();
		foreach($this->particles as $key => $particle) {
			if($particle->distance($block) <= 2) {
				$particle->setInvisible();
				$block->getLevel()->addParticle($particle);
				unset($this->particles[$key]);
				return;
			}
		}
	}

	/**
	 * @priority MONITOR
	 * @ignoreCancelled true
	 *
	 * @param PlayerJoinEvent $ev
	 */
	public function onJoin(PlayerJoinEvent $ev) {
		//TODO: make multiworld compatible
		foreach($this->particles as $particle) {
			$p = $particle->encode();
			foreach($p as $pk) {
				$ev->getPlayer()->dataPacket($pk);
			}
		}
	}

	/**
	 * @priority LOW
	 * @ignoreCancelled false
	 *
	 * @param PlayerInteractEvent $ev
	 */
	public function onInteract(PlayerInteractEvent $ev) {
		/** @var Chest $tile */
		$tile = $ev->getBlock()->getLevel()->getTile($ev->getBlock());
		if($ev->getBlock()->getId() === Block::CHEST and
			in_array($tile->getName(), $this->getConfig()->getAll(true)) and
			$ev->getItem()->getId() === Item::TRIPWIRE_HOOK and
			in_array($ev->getItem()->getName(), $this->getConfig()->getAll(true))
		) {
			$ev->setCancelled();
			$block = $ev->getBlock();
			$block->x <= 0 ? $pos = $block->add(0.5,1.5) : $pos = $block->add(-0.5,1.5);
			$block->z <= 0 ? $pos = $pos->add(0,0,0.5) : $pos = $pos->add(0,0,-0.5);
			$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new class($this, $ev->getPlayer(), $tile->getName(), $pos) extends PluginTask {
				private $player;
				private $name;
				private $current = 0;
				private $last = -1;
				private $pos;
				public function __construct(Plugin $owner, Player $player, string $name, Vector3 $pos) {
					parent::__construct($owner);
					$this->player = $player->getName();
					$this->name = $name;
					$this->pos = $pos;
				}
				public function onRun(int $currentTick) {
					$player = $this->getOwner()->getServer()->getPlayerExact($this->player);
					if($player === null) {
						$this->getHandler()->remove();
						return;
					}
					/** @var string[] $arr */
					$arr = $this->getOwner()->getConfig()->get($this->name, []);
					$r = array_rand($arr);
					while($r === $this->last) {
						$r = array_rand($arr);
					}
					$this->last = $r;
					$rand = $arr[$r];
					if($this->current <= 3 * count($arr)) {
						$str = explode(" ", $rand);
						$player->addTitle(TextFormat::DARK_BLUE.str_replace("_"," ", $str[0]), "", 0, 60, 0);
						$this->current++;
					}else{
						/** @var Item $item */
						/** @noinspection PhpUndefinedMethodInspection */
						$item = $this->getOwner()->getRandomItem($this->name);
						$player->addTitle(TextFormat::DARK_BLUE.str_replace("_"," ", $item->getName()), "", 0, 100, 0);

						$level = $player->getLevel();

						$level->addParticle(new HugeExplodeSeedParticle($this->pos));

						$pk = new LevelSoundEventPacket();
						$pk->sound = LevelSoundEventPacket::SOUND_EXPLODE;
						$pk->pitch = 1;
						$pk->extraData = -1;
						$pk->unknownBool = false;
						$pk->disableRelativeVolume = false;
						$pk->position = $this->pos;
						$player->dataPacket($pk);

						$player->getInventory()->addItem($item);
						$this->getHandler()->remove();
					}
				}
			},5,20);
		}elseif($ev->getBlock()->getId() === Block::CHEST and
			in_array($tile->getName(), $this->getConfig()->getAll(true)) and
			$ev->getItem()->getId() !== Item::TRIPWIRE_HOOK
		) {
			$ev->setCancelled();
		}
	}
}