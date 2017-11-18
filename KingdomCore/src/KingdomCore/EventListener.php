<?php

namespace KingdomCore;

use KingdomCore\task\JoinUITask;
use pocketmine\utils\Config;

class EventListener implements \pocketmine\event\Listener{

    public $plugin;

    public function __construct(Main $plugin){
           $this->plugin = $plugin;  
    }
    
    public function onJoin(\pocketmine\event\player\PlayerJoinEvent $e){
          $p = $e->getPlayer();
          $this->plugin->cfg[strtolower($p->getName())] = null;
          if($this->plugin->isNew($p->getName())){
              $this->plugin->registerPlayer($p->getName());
              $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new JoinUITask($p, $this->plugin), 20);
          }else{
              $p->sendMessage("Welcome back, " . $p->getName());
              $this->plugin->configs[strtolower($p->getName())] = new Config($this->plugin->getDataFolder() . "/players/" . $p->getName() . ".yml");
          }
    }

    public function onInteract(\pocketmine\event\player\PlayerInteractEvent $e){
        $p = $e->getPlayer();
        if($p->getInventory()->getItemInHand()->getName() == "Choose a Rank"){
            $this->plugin->sfui($p);
        }
    }
}
