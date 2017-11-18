<?php

namespace KingdomCore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\Config;

class Main extends \pocketmine\plugin\PluginBase{
  
   public $configs = [];
   public $joining = [];
   public $cfg = [];
   public $requester = [];
   public $am = [];
  
  public function onEnable(){
     @mkdir($this->getDataFolder());
     @mkdir($this->getDataFolder() . "/players");
     @mkdir($this->getDataFolder() . "/kingdoms");
     $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
     if(!file_exists($this->getDataFolder() . "/kingdoms/Dracosis.json")){
         $this->registerKingdom("Dracosis");
     }
      if(!file_exists($this->getDataFolder() . "/kingdoms/Dracosis.json")){
          $this->registerKingdom("Dracosis");
          $this->getLogger()->info('Registring KINGDOM!!!');
      }
      if(!file_exists($this->getDataFolder() . "/kingdoms/Quay.json")){
          $this->registerKingdom("Quay");
          $this->getLogger()->info('Registring KINGDOM!!!');
      }
      if(!file_exists($this->getDataFolder() . "/kingdoms/Cordian.json")){
          $this->registerKingdom("Cordian");
          $this->getLogger()->info('Registring KINGDOM!!!');
      }

     $this->saveDefaultConfig();
  }
  
  public function registerKingdom($kingdom){
      $config = new Config($this->getDataFolder() . "/kingdoms/" . $kingdom . ".json", Config::JSON, ["power" => 0, "king" => "name", "members" => []]);
      
  }

  public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch(strtolower($command->getName())){
            case "vote";
              $sender->getInventory()->addItem(Item::get(131, 0,1)->setCustomName("Choose a Rank"));
            break;
            case "k";
            switch($args[0]){
                case "info";
                    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                    $form = $api->createSimpleForm(function(Player $player, array $data){
                        $result = $data{0};
                        if(is_null($result)){
                            return false;
                        }
                        if($result == 0){
                            $player->sendMessage("test");
                            $this->sendReqUI($player);
                        }
                        var_dump($result);
                    });
                    $form->setTitle("§aKingdom Info");
                    $form->setContent(" §ePower: §b" . $this->getKingdomData($this->getKingdom($sender), "power") . "\n " . "§eKing: §b" . $this->getKingdomData($this->getKingdom($sender), "king"));
                    var_dump($this->getKingdomData($this->getKingdom($sender))->get("members"));
                    $form->addButton("§dKingdom Booty");
                    $form->sendToPlayer($sender);
            }
            break;
            case "money";
                $config = new Config($this->getDataFolder() . "/players/" . $sender->getName() . ".json");
                $sender->sendMessage("Money: " . $config->get("money"));
            break;
            case "warpme";
                $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                $form = $api->createSimpleForm(function(Player $player, array $data){
                    $result = $data{0};
                    if(is_null($result)){
                        return false;
                    }
                    switch($result){
                        case 0;
                            $this->addJoinDest("Dracosis", $player);
                            break;
                        case 1;
                            $this->addJoinDest("Quay", $player);
                            break;
                        case 2;
                            $this->addJoinDest("Cordian", $player);
                    }
                    var_dump($result);
                });
                $form->setTitle("§aSelect Warp");
                //   $form->addLabel('Select gamemode by clicking button.');
                $form->setContent("Please choose your kingdom in the list below");
                $form->addButton('Dracosis');
                $form->addButton('Quay');
                $form->addButton('Cordian');
                $form->sendToPlayer($sender);
                break;
        }
        return false;
  }

  public $kitlast = [];

  public function sfui($player){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
      $form = $api->createSimpleForm(function(Player $player, array $data){
          $result = $data{0};
          if(is_null($result)){
              return false;
          }
          switch($result){
              case 0;
                  $this->sendKitInfoUI($player, "Knight description here", "knight");
                  $this->kitlast[strtolower($player->getName())] = "knight";
                  break;
              case 1;
                  $this->sendKitInfoUI($player, "Farmer description here", "farmer");
                  $this->kitlast[strtolower($player->getName())] = "farmer";
                  break;
              case 2;
                  $this->sendKitInfoUI($player, "Gladiator desc", "gladiator");
                  $this->kitlast[strtolower($player->getName())] = "gladiator";
                  break;
              case 3;
                  $this->sendKitInfoUI($player, "Horseback desc.", "horseback");
                  $this->kitlast[strtolower($player->getName())] = "horseback";
                  break;
              case 4;
                  $this->sendKitInfoUI($player, "Mage desc.", "mage");
                  break;
          }
          var_dump($result);
      });
      $form->setTitle("§aVote");
      $form->addButton("Knight");
      $form->addButton("Farmer");
      $form->addButton("Gladiator");
      $form->addButton("Horseback");
      $form->addButton("Mage");
      $form->sendToPlayer($player);
  }

  public function sendKitInfoUI($player, $info, $kit){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
      $form = $api->createSimpleForm(function(Player $player, array $data){
          $result = $data{0};
          if(is_null($result)){
              return false;
          }
          if($result == 0){
             $this->giveKit($this->kitlast[strtolower($player->getName())], $player);
          }

          var_dump($result);
      });
      $form->setContent($info);
      $form->addButton("Select kit");
      $form->sendToPlayer($player);
  }


  public function giveKit($kit, $player){
      switch($kit){
          case "knight";
          $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup " . $player->getName() . "Knight 24");
          break;
          case "farmer";
              $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup " . $player->getName() . "Farmer 24");
          break;
          case "gladiator";
              $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup " . $player->getName() . "Gladiator 24");
          break;

          case "horseback";
              $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup " . $player->getName() . "Horseback 24");
          break;

          case "mage";
              $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup " . $player->getName() . "Mage 24");
          break;
      }
  }

  public function getCfg($player){
      if($this->cfg[strtolower($player->getName())] == null) {
          $this->cfg[strtolower($player->getName())] = new Config($this->getDataFolder() . "/players/" . $player->getName() . ".json");
      }
          return $this->cfg[strtolower($player->getName())];
  }

  public function getKingdom($player){
      $config = new Config($this->getDataFolder() . "/players/" . $player->getName() . ".json");
      return $config->get("kingdom");
  }

  public function sendReqUI($player)
  {
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
      $form = $api->createCustomForm(function(Player $player, array $data){
          $result = $data{0};
          if(is_null($result)){
              return false;
          }
          if($result == 1) {
              if ($pl = $this->getServer()->getPlayerExact($this->getKingdomData($this->getKingdom($player))->get("king")) instanceof Player) {
                  $player->sendMessage("§eRequested §b" . $result{0} . " §emoney from king.");
                  $this->requester[strtolower($pl->getName())] = $player->getName();
                  $this->am[strtolower($pl->getName())] = $result{0};
                  //$pl->sendMessage("§aYou have money request from " . $player->getName() . " , it's " . $result{0});
              }else{
                  $player->sendMessage("§cKing is not online right now");
              }
          }
          if($result == 0){
              $player->sendMessage("cc");
              if(preg_match("/[a-z]/i", $result{0})) {
                  $player->sendMessage("§cYou must enter number!");
              }
          }
          var_dump($result);
      });
      $form->addInput('Amount of money to request');
      $form->sendToPlayer($player);
  }

  public function sendRequestResUI($player, $requester, $am){
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
      $form = $api->createSimpleForm(function(Player $player, array $data){
          $result = $data{0};
          if(is_null($result)){
              $player->sendMessage("§aRequest was automatically closed");
              return false;
          }
          if($result == 0){
              $player->sendMessage("Request accepted, money sent.");
              if($pl = $this->getServer()->getPlayerExact($this->requester[strtolower($player->getName())])){
                  $pl->sendMessage("King accepted request");
                  $this->addMoney($pl, $this->am[strtolower($player->getName())]);
              }
          }
          if($result == 1){
              $player->sendMessage("Request cancelled");
              if($pl = $this->getServer()->getPlayerExact($this->requester[strtolower($player->getName())])){
                  $pl->sendMessage("King cancelled your money request");
              }
          }

          var_dump($result);
      });
      $form->setTitle("Request from " . $requester);
      $form->setContent("You have money request from " . $requester . ", by clicking 'yes' you send him " . $am . "money and by 'no' u cancell request.");
      $form->addButton('Yes');
      $form->addButton('No');
  }

  public function addMoney($player, $i){
     $config = $this->getCfg($player);
      $money = $config->get("money") + $i;
      $config->set("money", $money);
      $config->save();
  }

  public function getKingdomData($kingdom, $args = null){
      $config = new Config($this->getDataFolder() . "/kingdoms/" . $kingdom . ".json");
      if($args !== null) {
          return $config->get($args);
      }else{
          return $config;
      }
  }

  public function addJoinDest($dest, $player){
      $this->joining[strtolower($player->getName())] = $dest;
      $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
      $form = $api->createSimpleForm(function(Player $player, array $data){
          $result = $data{0};
          if(is_null($result)){
              return false;
          }
          switch($result){
              case 0;
                  $player->sendMessage("§aJoining " . $this->joining[strtolower($player->getName())] . " Kingdom");
                  echo $this->getConfig()->get($this->joining[strtolower($player->getName())] . "Kingdom");
                  $player->teleport($this->getServer()->getLevelByName($this->getConfig()->get($this->joining[strtolower($player->getName())] . "Kingdom"))->getSafeSpawn());
                  break;
              case 1;
                  $player->sendMessage("§aJoining " . $this->joining[strtolower($player->getName())] . " Village");
                  echo $this->getConfig()->get($this->joining[strtolower($player->getName())] . "Village");
                  $player->teleport($this->getServer()->getLevelByName($this->getConfig()->get($this->joining[strtolower($player->getName())] . "Village"))->getSafeSpawn());
                  break;
          }
          var_dump($result);
      });
      $form->setTitle("§aSelect if Village or Kingdom");
      //   $form->addLabel('Select gamemode by clicking button.');
      $form->setContent("Please choose destination you want join in the list below");
      $form->addButton('Kingdom');
      $form->addButton('Village');
      $form->sendToPlayer($player);
  }
  
  
  public function addToKingdom($player, $kingdom){
     $config = $this->configs[strtolower($player->getName())];
     $config->set("kingdom", $kingdom);
     $config->save();
     $array = $this->getKingdomData($this->getKingdom($player))->get("members");
     $array[$player->getName()];
  //   $array[] = $player->getName();
     $this->getKingdomData($this->getKingdom($player))->set("membersc", "kkt");
     $this->getKingdomData($this->getKingdom($player))->save();
     echo 'ADDED!!!!!';
  }


 
  public function isNew($player){
    if(!file_exists($this->getDataFolder() . "/players/" . $player . ".json")){
      return true;
    }else{
      return false;
    }
  }
  
  public function registerPlayer($player){
    $config = new Config($this->getDataFolder() . "/players/" . $player . ".json", Config::JSON, ["kingdom" => 0, "money" => 0, "power" => 0]);
    $this->configs[strtolower($player)] = new Config($this->getDataFolder() . "/players/" . $player . ".json");
  }
  
}
