<?php
declare(strict_types=1);
namespace jasonwynn10\DeathBan;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	/** @var PluginTask[] $tasks */
	private $tasks = [];
	/** @var string[] $noPvp */
	public $noPvp = [];
	const TIMER = 30;
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @priority MONITOR
	 *
	 * @param PlayerDeathEvent $event
	 */
	public function onDeath(PlayerDeathEvent $event) {
		$expiration = new \DateTime();
		$expiration->add(new \DateInterval('PT45M'));
		$this->getServer()->getNameBans()->addBan($event->getPlayer()->getName(), "This server is hardcore.\nYou may rejoin once your 45 minutes are up.", $expiration, $this->getDescription()->getName());
		$event->getPlayer()->kick("This server is hardcore.\nYou may rejoin in 45 minutes.", false);
	}

	/**
	 * @priority MONITOR
	 * @ignoreCancelled false
	 *
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event) {
		if($event->isCancelled())
			return;
		$task = new class($this, $event->getPlayer()) extends PluginTask {
			private $player;
			private $firstTick = 0;
			public function __construct(Plugin $owner, Player $player) {
				parent::__construct($owner);
				$this->player = $player->getName();
			}
			public function onRun(int $currentTick) {
				$player = $this->getOwner()->getServer()->getPlayerExact($this->player);
				if($player !== null) {
					if($this->firstTick === 0) {
						$player->sendTip(TextFormat::YELLOW."PvpTimer: self::TIMER Minutes");
						$this->firstTick = $currentTick;
					}elseif(($currentTick - $this->firstTick) * 20 * 60 >= Main::TIMER) {
						$key = array_search($this->player, $this->getOwner()->noPvp);
						if($key !== false) {
							unset($this->getOwner()->noPvp[$key]);
						}
						$this->getHandler()->remove();
					}else{
						$time = ($currentTick - $this->firstTick) * 20 * 60;
						$player->sendTip(TextFormat::YELLOW."PvpTimer: ".(int)$time." Minutes");
					}
				}
			}
		};
		$this->tasks[$event->getPlayer()->getName()] = $task;
		$this->getServer()->getScheduler()->scheduleRepeatingTask($task, 20 * 60);
	}

	/**
	 * @priority LOW
	 * @ignoreCancelled false
	 *
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event) {
		/** @var Player $player */
		/** @var Player $damager */
		if(!$event->isCancelled() and $event instanceof EntityDamageByEntityEvent and ($player = $event->getEntity()) instanceof Player and ($damager = $event->getDamager()) instanceof Player) {
			if(in_array($player->getName(), $this->noPvp)) {
				$event->setCancelled();
			}
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
		if($sender instanceof Player) {
			$this->tasks[$sender->getName()]->getHandler()->remove();
			$key = array_search($sender->getName(), $this->noPvp);
			if($key !== false) {
				unset($this->noPvp[$key]);
			}
		}
		return true;
	}
}