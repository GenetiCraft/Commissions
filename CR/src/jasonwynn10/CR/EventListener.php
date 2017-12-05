<?php
declare(strict_types=1);
namespace jasonwynn10\CR;

use jasonwynn10\CR\form\KingdomSelectionForm;
use jasonwynn10\CR\form\MoneyGrantRequestForm;
use jasonwynn10\CR\task\FormsRepairTask;
use onebone\economyapi\EconomyAPI;
use onebone\economyapi\event\money\AddMoneyEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class EventListener implements Listener {
	/** @var Main $plugin */
	private $plugin;
	/** @var int[] $sentForms */
	public static $sentForms = [];

	/**
	 * EventListener constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
		$this->plugin = $plugin;
		$plugin->getLogger()->debug("Event Listener Registered!");
	}

	/**
	 * @priority MONITOR
	 *
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event) {
		$kingdom = $this->plugin->getPlayerKingdom($event->getPlayer());
		if($kingdom === null) {
			Main::sendPlayerDelayedForm($event->getPlayer(), new KingdomSelectionForm());
			return;
		}
		if($this->plugin->getKingdomLeader($kingdom) === $event->getPlayer()->getName()) {
			foreach($this->plugin->getMoneyRequestsInQueue() as $requester => $amount) {
				Main::sendPlayerDelayedForm($event->getPlayer(), new MoneyGrantRequestForm($requester, $amount));
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
				$this->plugin->getLogger()->debug($event->getUsername()." has been taxed!");
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

	/**
	 * @priority MONITOR
	 * @ignoreCancelled true
	 *
	 * @param DataPacketSendEvent $event
	 */
	public function onDataPacketSend(DataPacketSendEvent $event) {
		$packet = $event->getPacket();
		if($packet instanceof ModalFormRequestPacket) {
			$data = json_decode($packet->formData);
			if(in_array($data->title, [
				"Kingdom Information",
				"Kingdom Selection",
				"Kingdom Warp Menu",
				"Request Money",
				"Starting Location",
				"Vote",
				"Rank Information"
			])) {
				self::$sentForms[] = $packet->formId;
				$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new FormsRepairTask($this->plugin, $event->getPlayer()->getName(), $packet), 20*60); //TODO: optimize timing
			}elseif(strpos($data["title"], "Money Requested from")) {
				self::$sentForms[] = $packet->formId;
				$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new FormsRepairTask($this->plugin, $event->getPlayer()->getName(), $packet), 20*60); //TODO: optimize timing
			}
		}
	}

	/**
	 * @priority MONITOR
	 * @ignoreCancelled true
	 *
	 * @param DataPacketReceiveEvent $event
	 */
	public function onDataPacketReceive(DataPacketReceiveEvent $event) { //TODO: find a better method than Receive event
		$packet = $event->getPacket();
		if($packet instanceof ModalFormResponsePacket and in_array($packet->formId, self::$sentForms)) {
			unset(self::$sentForms[array_search($packet->formId, self::$sentForms)]);
		}
	}
}