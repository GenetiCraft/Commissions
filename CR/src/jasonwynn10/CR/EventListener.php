<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use jasonwynn10\CR\form\KingdomSelectionForm;
use jasonwynn10\CR\form\MoneyGrantRequestForm;
use jasonwynn10\CR\task\DelayedFormTask;
use onebone\economyapi\EconomyAPI;
use onebone\economyapi\event\money\AddMoneyEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener {
	/** @var Main $plugin */
	private $plugin;

	/**
	 * EventListener constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
		$this->plugin = $plugin;
	}

	/**
	 * @priority MONITOR
	 *
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event) {
		$kingdom = $this->plugin->getPlayerKingdom($event->getPlayer());
		if($kingdom === null) {
			$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new DelayedFormTask($this->plugin, new KingdomSelectionForm(), $event->getPlayer()), 20*3);
			return;
		}
		if($this->plugin->getKingdomLeader($kingdom) === $event->getPlayer()->getName()) {
			foreach($this->plugin->getMoneyRequestsInQueue() as $requester => $amount) {
				$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new DelayedFormTask($this->plugin, new MoneyGrantRequestForm($requester, $amount), $event->getPlayer()), 20*60*3);
			}
		}
	}

	/**
	 * @priority LOW
	 * @ignoreCancelled true
	 *
	 * @param AddMoneyEvent $event
	 */
	public function onEarnMoney(AddMoneyEvent $event) {
		if(!$event->isCancelled() and $event->getIssuer() !== "cr") {
			$kingdom = $this->plugin->getPlayerKingdom($this->plugin->getServer()->getOfflinePlayer($event->getUsername())->getPlayer() ?? $this->plugin->getServer()->getOfflinePlayer($event->getUsername()));
			if($kingdom !== null) {
				$event->setCancelled();
				$amount = $event->getAmount();
				$percent = abs((int)$this->plugin->getConfig()->getNested("Taxes.".$kingdom, 2)) / 100;
				$economy = EconomyAPI::getInstance();
				$economy->addMoney($kingdom."Kingdom", $percent * $amount, false, "cr");
				$economy->addMoney($event->getUsername(), $amount - ($percent * $amount), false, "cr");
			}
		}
	}

	/**
	 * @priority HIGHEST
	 * @ignoreCancelled false
	 *
	 * @param PlayerChatEvent $event
	 */
	public function onPlayerChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();
		$kingdom = $this->plugin->getPlayerKingdom($player);
		if($kingdom === null)
			return;
		$format = str_replace("{kingdom}", $kingdom, $event->getFormat());
		$format = str_replace("{isLeader}", $this->plugin->getKingdomLeader($kingdom) === $player->getName() ? "Leader" : "", $format);
		$event->setFormat($format);
	}
}