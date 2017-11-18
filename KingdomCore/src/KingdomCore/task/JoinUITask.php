<?php

namespace KingdomCore\task;
use KingdomCore\Main;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class JoinUITask extends \pocketmine\scheduler\Task{
  
  public $time = 2; //LOL HACKY
  
  public $plugin;
  public $player;
  
  public function __construct(\pocketmine\Player $player, Main $plugin){
    $this->player = $player;
    $this->plugin = $plugin;
  }
 
  public function onRun(int $currentTick){
     $this->time--;
     $this->player->sendMessage("cype");
     if($this->time == 0){
         $this->player->sendMessage("0");
           $api = $this->plugin->getServer()->getPluginManager()->getPlugin("FormAPI");
           $form = $api->createSimpleForm(function(Player $player, array $data){
               $result = $data{0};
               if(is_null($result)){
                   return false;
               }
               if($result == 1){
                   $player->sendMessage("§aYou are now in Kingdom Dracosis");
                    $this->plugin->addToKingdom($player, "Dracosis");
               }elseif($result == 2){
                   $this->plugin->addToKingdom($player, "Quay");
                   $player->sendMessage("Quay");
               }elseif($result == 3){
                  $this->plugin->addToKingdom($player, "Cordian");
                   $player->sendMessage("Cordian");
               }
               var_dump($result);
           });
           $form->setTitle("§aSelect Kingdom");
        //   $form->addLabel('Select gamemode by clicking button.');
         $form->setContent("Hello young traveller! Welcome to your future! This small choice will have a BIG impact on your stay here. Choose wisely what Kingdom you shall server!");

         $form->addButton("Please choose your kingdom", 1, "");
         $form->addButton(TextFormat::BOLD . "Dracosis", 1, "https://vignette3.wikia.nocookie.net/scream-queens/images/e/ed/Gold_Crown_with_Red_Diamonds_PNG_Clipart.png/revision/latest?cb=20161002074942");
         $form->addButton(TextFormat::BOLD . "Quay", 1, "http://icons.iconarchive.com/icons/chrisl21/minecraft/512/Diamond-Sword-icon.png");
         $form->addButton(TextFormat::BOLD . "Cordian", 1, "https://vignette.wikia.nocookie.net/articleinsanityrecreation/images/b/ba/Minecraft_Diamond_body.png/revision/latest?cb=20140628182144");
           $form->sendToPlayer($this->player);
         $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
     }
  }
}
