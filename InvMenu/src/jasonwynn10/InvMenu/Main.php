<?php
namespace jasonwynn10\InvMenu;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	public function onEnable(){
		@mkdir($this->getDataFolder());
		new Config($this->getDataFolder()."config.yml", Config::YAML, [
			"OIQT" => "0.0.0.0:19132",
			"Factions" => "0.0.0.0:19132",
			"Skyblock" => "0.0.0.0:19132",
			"UHC" => "0.0.0.0:19132",
			"TeamKill" => "0.0.0.0:19132"
		]);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onJoin(PlayerJoinEvent $event) {
		$inventory = $event->getPlayer()->getInventory();
		$inventory->clearAll();
		$inventory->setContents(
			[
				Item::get(Item::CLOCK)->setCustomName(TextFormat::GREEN."Server"),
				Item::get(Item::BOOK)->setCustomName(TextFormat::YELLOW."Info")
			]);
		for($slot = 0; $slot <= $inventory->getHotbarSize(); $slot++) {
			$inventory->equipItem($slot); //hack for resetting hotbar positions
		}
		$inventory->equipItem(0);
	}
	public function onTap(PlayerInteractEvent $event) {
		$item = $event->getItem();
		if($item->getId() === Item::CLOCK and stripos($item->getName(), "server") !== false) {
			$inventory = $event->getPlayer()->getInventory();
			$inventory->clearAll();
			$inventory->setContents(
				[
					Item::get(Item::PAPER)->setCustomName("OIQT"),
					Item::get(Item::PAPER)->setCustomName("Factions"),
					Item::get(Item::PAPER)->setCustomName("Skyblock"),
					Item::get(Item::PAPER)->setCustomName("UHC"),
					Item::get(Item::PAPER)->setCustomName("TeamKill")
				]);
			for($slot = 0; $slot <= $inventory->getHotbarSize(); $slot++) {
				$inventory->equipItem($slot); //hack for resetting hotbar positions
			}
			$inventory->equipItem(0);
		}elseif($item->getId() === Item::BOOK and stripos($item->getName(), "info") !== false) {
			$event->getPlayer()->sendTip(TextFormat::BLUE."Twitter: @RavikalMC");
		}elseif($item->getId() === Item::PAPER and stripos($item->getName(), "oiqt") !== false) {
			//OIQT
			$address = $this->getConfig()->get("OIQT", $this->getServer()->getIp().":".$this->getServer()->getPort());
		}elseif($item->getId() === Item::PAPER and stripos($item->getName(), "factions") !== false) {
			//Factions
			$address = $this->getConfig()->get("Factions", $this->getServer()->getIp().":".$this->getServer()->getPort());
		}elseif($item->getId() === Item::PAPER and stripos($item->getName(), "skyblock") !== false) {
			//Skyblock
			$address = $this->getConfig()->get("Skyblock", $this->getServer()->getIp().":".$this->getServer()->getPort());
		}elseif($item->getId() === Item::PAPER and stripos($item->getName(), "uhc") !== false) {
			//UHC
			$address = $this->getConfig()->get("UHC", $this->getServer()->getIp().":".$this->getServer()->getPort());
		}elseif($item->getId() === Item::PAPER and stripos($item->getName(), "teamkill") !== false) {
			//TeamKill
			$address = $this->getConfig()->get("TeamKill", $this->getServer()->getIp().":".$this->getServer()->getPort());
		}
		if(isset($address)) {
			$address = explode(";", $address);
			$event->getPlayer()->transfer($address[0], $address[1]);
		}
	}
}