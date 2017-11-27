<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use jasonwynn10\CR\form\KingdomSelectionForm;
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
	public function onJoin(PlayerJoinEvent $event) {
		$form = new KingdomSelectionForm($this->plugin);
		$event->getPlayer()->sendForm($form);
	}
}