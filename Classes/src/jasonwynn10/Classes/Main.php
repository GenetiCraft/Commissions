<?php
namespace jasonwynn10\Classes;

use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;

class Main extends PluginBase implements Listener {
	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onArmourChange(EntityArmorChangeEvent $ev) {
		$entity = $ev->getEntity();
		if($entity instanceof Player) {
			$this->getServer()->getScheduler()->scheduleDelayedTask(new class($this, $entity->getName()) extends PluginTask {
				private $player;
				public function __construct(Plugin $owner, string $player) {
					parent::__construct($owner);
					$this->player = $player;
				}
				public function onRun(int $currentTick) {
					/** @var Player|null $entity */
					$entity = $this->getOwner()->getServer()->getPlayer($this->player);
					if(count($entity->getInventory()->getArmorContents()) === 4) {
						/** @var Item[] $slots */
						$slots = $entity->getInventory()->getArmorContents();
						if(
							$slots[0]->getId() === Item::LEATHER_HELMET and
							$slots[1]->getId() === Item::LEATHER_CHESTPLATE and
							$slots[2]->getId() === Item::LEATHER_LEGGINGS and
							$slots[3]->getId() === Item::LEATHER_BOOTS
						) {
							$entity->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(2)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setDuration(INT32_MAX));
							return;
						}
						if(
							$slots[0]->getId() === Item::IRON_HELMET and
							$slots[1]->getId() === Item::IRON_CHESTPLATE and
							$slots[2]->getId() === Item::IRON_LEGGINGS and
							$slots[3]->getId() === Item::IRON_BOOTS
						) {
							$entity->addEffect(Effect::getEffect(Effect::NIGHT_VISION)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::FIRE_RESISTANCE)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::HASTE)->setAmplifier(1)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(INT32_MAX));
							return;
						}
						if(
							$slots[0]->getId() === Item::GOLD_HELMET and
							$slots[1]->getId() === Item::GOLD_CHESTPLATE and
							$slots[2]->getId() === Item::GOLD_LEGGINGS and
							$slots[3]->getId() === Item::GOLD_BOOTS
						) {
							$entity->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(1)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(1)->setDuration(INT32_MAX));
							return;
						}
						if(
							$slots[0]->getId() === Item::CHAIN_HELMET and
							$slots[1]->getId() === Item::CHAIN_CHESTPLATE and
							$slots[2]->getId() === Item::CHAIN_LEGGINGS and
							$slots[3]->getId() === Item::CHAIN_BOOTS
						) {
							$entity->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier(1)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(2)->setDuration(INT32_MAX));
							$entity->addEffect(Effect::getEffect(Effect::STRENGTH)->setDuration(INT32_MAX));
							return;
						}
						$entity->removeAllEffects();
					}
				}
			}, 5); // check armor types after transaction completed
		}
	}
	public function onTap(PlayerInteractEvent $event) {
		//TODO: tap cooldown
		if($event->getItem()->getId() === Item::GHAST_TEAR) {
			//Regen 1 for 5 secs
		}
		if($event->getItem()->getId() === Item::MAGMA_CREAM) {
			//Fire resistance 1 for 5 secs
		}
		if($event->getItem()->getId() === Item::SUGAR) {
			//speed 2 for 5 secs
		}
		if($event->getItem()->getId() === Item::GOLDEN_CARROT) {
			//night vision for 5 secs
		}
		if($event->getItem()->getId() === Item::BLAZE_ROD) {
			//strength 1 for 5 secs
		}
		if($event->getItem()->getId() === Item::FEATHER) {
			//jump 2 for 5 secs
		}
	}
}