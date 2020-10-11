<?php

namespace SkyBlockSettings;

use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\command\{Command, CommandExecutor, CommandSender, ConsoleCommandSender};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\level\{Position, Level};
use pocketmine\utils\{TextFormat, Config};
use pocketmine\Server;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\{PlayerRespawnEvent, PlayerMoveEvent, PlayerLoginEvent, PlayerQuitEvent, PlayerInteractEvent, PlayerJoinEvent};
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use jojoe77777\FormAPI\{CustomForm, SimpleForm, FormAPI, ModalForm, Form};

use room17\SkyBlock\SkyBlock;
use room17\SkyBlock\session\Session;
use room17\SkyBlock\island\RankIds;

class Main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("SkyBlockSettings v1.0.0");
	     	$this->trust = new Config($this->getDataFolder() . "Trust-Player.yml", Config::YAML, []);
        $this->sb = $this->getServer()->getPluginManager()->getPlugin("SkyBlock");
    }

  /*
      public function SFLY(Player $sender):bool{
      $form = new SimpleForm(function (Player $player, $data) {
      $result = $data;
      if ($result == null) {
      }
      switch ($result) {
      case 0:
      break;
      
      case 1:
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$player->getLevel()->getName().' fly off');
      $player->sendMessage("§9島嶼管理 > §c已關閉島嶼飛行");
      break;

      case 2:
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$player->getLevel()->getName().' fly on');
      $player->sendMessage("§9島嶼管理 > §c已開啟島嶼飛行");
      break;
            
    }
  });
  $form->setTitle("§l§9島嶼管理 > 飛行開關");
  $form->addButton("§c關閉",0,"textures/ui/cancel");	
  $form->addButton("§f關閉島嶼飛行",0,"textures/ui/icon_lock");						
  $form->addButton("§f開啟島嶼飛行",0,"textures/ui/icon_unlocked");						
  $form->sendToPlayer($sender);
  return true;
}
*/

      public function PvP(Player $sender):bool{
      $form = new SimpleForm(function (Player $player, $data) {
      $result = $data;
      if ($result == null) {
      }
      switch ($result) {
      case 0:
      break;
      
      case 1:
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$player->getLevel()->getName().' pvp off');
      $player->sendMessage("§9島嶼管理 > §c已關閉島嶼PvP");
      break;

      case 2:
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$player->getLevel()->getName().' pvp on');
      $player->sendMessage("§9島嶼管理 > §c已開啟島嶼PvP");
      break;
            
    }
  });
  $form->setTitle("§l§9島嶼管理 > PvP開關");
  $form->addButton("§c關閉",0,"textures/ui/cancel");	
  $form->addButton("§f關閉島嶼PvP",0,"textures/ui/icon_lock");						
  $form->addButton("§f開啟島嶼PvP",0,"textures/ui/icon_unlocked");						
  $form->sendToPlayer($sender);
  return true;
}

public function UnItem($sender){
  $form = new CustomForm(function(Player $sender, $data){
      if($data === null){
          return true;
      }
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$sender->getLevel()->getName().' unbanitem '.$data);
  });
  $form->setTitle("§l§9島嶼系統 > 解禁物品");
  $form->addInput("請輸入已禁用的物品ID(數字)", "Ex.鑽石(264)");
  $form->sendToPlayer($sender);
  return $form;
}

public function UnCommand($sender){
  $form = new CustomForm(function(Player $sender, $data){
      if($data === null){
          return true;
      }
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$sender->getLevel()->getName().' unbancmd '.$data);
  });
  $form->setTitle("§l§9島嶼系統 > 解禁指令");
  $form->addInput("請輸入已禁用的指令", "Ex.VIP指令(v)");
  $form->sendToPlayer($sender);
  return $form;
}

public function BanItem($sender){
  $form = new CustomForm(function(Player $sender, $data){
      if($data === null){
          return true;
      }
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$sender->getLevel()->getName().' banitem '.$data);
  });
  $form->setTitle("§l§9島嶼系統 > 禁用物品");
  $form->addInput("請輸入要禁用的物品ID(數字)", "Ex.鑽石(264)");
  $form->sendToPlayer($sender);
  return $form;
}

public function BanCommand($sender){
  $form = new CustomForm(function(Player $sender, $data){
      if($data === null){
          return true;
      }
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$sender->getLevel()->getName().' bancmd '.$data);
  });
  $form->setTitle("§l§9島嶼系統 > 禁用指令");
  $form->addInput("請輸入要禁用的指令", "Ex.VIP指令(v)");
  $form->sendToPlayer($sender);
  return $form;
}

public function PlayerMax($sender){
  $form = new CustomForm(function(Player $sender, $data){
      if($data === null){
          return true;
      }
      $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp '.$sender->getLevel()->getName().' max '.$data);
  });
  $form->setTitle("§l§9島嶼系統 > 世界人數上限");
  $form->addInput("請輸入上限人數", "Ex.10人(10)");
  $form->sendToPlayer($sender);
  return $form;
}

          public function Command(Player $sender):bool{
          $form = new SimpleForm(function (Player $player, $data) {
          $result = $data;
          if ($result == null) {
          }
          switch ($result) {
            case 0:
            break;

            case 1:
            $this->BanCommand($player);
            break;

            case 2:
            $this->UnCommand($player);
            break;
          }
        });
        $form->setTitle("§l§9島嶼管理 > 指令管理");
        $form->addButton("§c關閉",0,"textures/ui/cancel");	
        $form->addButton("§l§9禁用指令",0,"textures/ui/icon_setting");	
        $form->addButton("§l§9解禁指令",0,"textures/ui/icon_setting");	
        $form->sendToPlayer($sender);
        return true;
    }

          public function Item(Player $sender):bool{
          $form = new SimpleForm(function (Player $player, $data) {
          $result = $data;
          if ($result == null) {
          }
          switch ($result) {
            case 0:
            break;

            case 1:
            $this->BanItem($player);
            break;

            case 2:
            $this->UnItem($player);
            break;
          }
        });
        $form->setTitle("§l§9島嶼管理 > 物品管理");
        $form->addButton("§c關閉",0,"textures/ui/cancel");	
        $form->addButton("§l§9禁用物品",0,"textures/ui/icon_setting");	
        $form->addButton("§l§9解禁物品",0,"textures/ui/icon_setting");	
        $form->sendToPlayer($sender);
        return true;
    }

          public function Limit(Player $sender):bool{
          $form = new SimpleForm(function (Player $player, $data) {
          $result = $data;
          if ($result == null) {
          }
          switch ($result) {
            case 0:
            break;

            case 1:
            $this->PlayerMax($player);
            break;
          }
        });
        $form->setTitle("§l§9島嶼管理 > 島嶼人數管理");
        $form->addButton("§c關閉",0,"textures/ui/cancel");	
        $form->addButton("§l§9調整上限人數",0,"textures/ui/icon_setting");	
        $form->sendToPlayer($sender);
        return true;
    }

          public function UI(Player $sender):bool{
          $form = new SimpleForm(function (Player $player, $data) {
          $result = $data;
          if ($result == null) {
          }
          switch ($result) {
            case 0:
            break;

            case 1:
            $this->PvP($player);
            break;

            case 2:
            $this->Command($player);
            break;
          
            case 3:
            $this->Item($player);
            break;

            case 4:
            $pn = strtolower($player->getName());
						if($this->trust->get($pn)){
            $this->Limit($player);
            }else{
            $player->sendMessage("請找尋服主SD解鎖本功能");
            }
            break;

            case 5:
            $this->getServer()->getCommandMap()->dispatch($player, "wp info ".$player->getLevel()->getName()."");
            break;
          }
        });
        $form->setTitle("§l§9島嶼管理 > 選項管理");
        $form->setContent("§f親愛的§3".$sender->getName()."§f請選擇你要的操作");
        $form->addButton("§c關閉",0,"textures/ui/cancel");	
        $form->addButton("§l§9PvP管理",0,"textures/ui/icon_setting");	
        $form->addButton("§l§9指令管理",0,"textures/ui/icon_setting");	
        $form->addButton("§l§9物品管理",0,"textures/ui/icon_setting");	
        $form->addButton("§l§9人數限制管理§b(需贊助取得)",0,"textures/ui/icon_setting");	
        $form->addButton("§l§9查看該世界(島嶼)信息",0,"textures/ui/icon_setting");
        $form->sendToPlayer($sender);
        return true;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
    $s = $this->sb->getSessionManager()->getSession($sender);
   
    switch($command->getName()){
			case "ats":
      if(!isset($args[0]) && !isset($args[1])){
      $sender->sendMessage("§c> /ats <玩家ID> <true / false>");
      return false;
      }
      $setname = strtolower($args[0]);
      $open = $args[1];
      $this->trust->set($setname, boolval($open));
      $this->trust->save();
      $sender->sendMessage("§e> 操作成功!");
      return true;	

    case "iss":
    if($s->getIsland() && $s->getIsland() === $s->getIslandByLevel() && $s->getRank() === RankIds::FOUNDER ){
    $this->UI($sender);
    }else{
    $sender->sendMessage("§c!!! §e你必須身為島主(並在你的島嶼)才可用本指令 §c!!!");
    }
  }
    return true;
  }


    public function onDisable(){
       
    }
}