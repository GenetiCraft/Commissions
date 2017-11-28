<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use jasonwynn10\CR\form\KingdomSelectionForm;
use jasonwynn10\CR\form\MoneyGrantRequestForm;
use pocketmine\event\Listener;
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
	 * @ignoreCancelled true
	 *
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event) {
		if($event->isCancelled())
			return;
		if($this->plugin->getPlayerKingdom($event->getPlayer()) === null) {
			$form = new KingdomSelectionForm();
			$event->getPlayer()->sendForm($form);
		}
		if($this->plugin->getKingdomLeader($this->plugin->getPlayerKingdom($event->getPlayer())) === $event->getPlayer()->getName()) {
			foreach($this->plugin->getMoneyRequestsInQueue() as $requester => $amount) {
				$event->getPlayer()->sendForm(new MoneyGrantRequestForm($requester, $amount));
			}
		}
	}
}