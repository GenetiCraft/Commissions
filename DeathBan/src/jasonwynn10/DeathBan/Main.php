<?php
namespace jasonwynn10\DeathBan;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @priority
	 *
	 * @param PlayerDeathEvent $event
	 */
	public function onDeath(PlayerDeathEvent $event) {
		$expiration = new \DateTime();
		$expiration->add(new \DateInterval('PT45M'));
		$this->getServer()->getNameBans()->addBan($event->getPlayer()->getName(), "This server is hardcore.\nYou may rejoin once your 45 minutes are up.", $expiration, "DeathBan Plugin");
		$event->getPlayer()->kick("This server is hardcore.\nYou may rejoin in 45 minutes.", false);
	}
}