<?php

namespace SkyBlockSettings;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config
use pocketmine\Server;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerRespawnEvent
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\FormAPI;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\Form;

use room17\SkyBlock\SkyBlock;
use room17\SkyBlock\session\Session;
use room17\SkyBlock\island\RankIds;

class Main extends PluginBase implements Listener {

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("SkyBlockSettings v1.0.0");
        $this->trust = new Config($this->getDataFolder() . "Trust-Player.yml", Config::YAML, []);
        $this->sb = $this->getServer()->getPluginManager()->getPlugin("SkyBlock");
    }

    /*public function sendFly(Player $sender): bool {
        $form = new SimpleForm(function (Player $player, $data) {
            $result = $data;
            if ($result == null) {

            }
            switch ($result) {
                case 0:
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $player->getLevel()->getName() . ' fly off');
                    $player->sendMessage(TextFormat::BLUE . "Island Management > " . TextFormat::RED . "Flight has been disabled");
                    break;
                case 1:
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $player->getLevel()->getName() . ' fly on');
                    $player->sendMessage(TextFormat::BLUE . "Island Management > " . TextFormat::RED . "Flight has been enabled");
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Flight Switch");
        $form->addButton("Disable Flight", 0, "textures/ui/icon_lock");
        $form->addButton("Enable Flight", 0, "textures/ui/icon_unlocked");
        $form->addButton(TextFormat::RED . "Cancel", 0, "textures/ui/cancel");
        $form->sendToPlayer($sender);
        return true;
    }*/

    public function sendPvP(Player $sender): bool {
        $form = new SimpleForm(function (Player $player, $data) {
            $result = $data;
            if ($result == null) {

            }
            switch ($result) {
                case 0:
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $player->getLevel()->getName() . ' pvp off');
                    $player->sendMessage(TextFormat::BLUE . "Island Management > " . TextFormat::RED . "PvP has been disabled");
                    break;
                case 1:
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $player->getLevel()->getName() . ' pvp on');
                    $player->sendMessage(TextFormat::BLUE . "Island Management > " . TextFormat::RED . "Pvp has been enabled");
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > PvP Switch");
        $form->addButton("Disable PvP", 0, "textures/ui/icon_lock");
        $form->addButton("Enable PvP", 0, "textures/ui/icon_unlocked");
        $form->addButton(TextFormat::RED . "Cancel", 0, "textures/ui/cancel");
        $form->sendToPlayer($sender);
        return true;
    }

    /**
     * Unbans an item
     * @return void
     */
    public function unbanItem($sender) {
        $form = new CustomForm(function (Player $sender, $data) {
            if ($data === null) {
                return true;
            }
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $sender->getLevel()->getName() . ' unbanitem ' . $data);
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Unban Items");
        $form->addInput("Please enter the banned item ID (number)", "Ex. Diamond (264)");
        $form->sendToPlayer($sender);
        return $form;
    }

    /**
     * Unbans a command
     * @return void
     */
    public function unbanCommand($sender) {
        $form = new CustomForm(function (Player $sender, $data) {
            if ($data === null) {
                return true;
            }
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $sender->getLevel()->getName() . ' unbancmd ' . $data);
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Unban Commands");
        $form->addInput("Please enter a disabled command", "Ex. VIP command (v)");
        $form->sendToPlayer($sender);
        return $form;
    }

    /**
     * Bans an item
     * @return void
     */
    public function banItem($sender) {
        $form = new CustomForm(function (Player $sender, $data) {
            if ($data === null) {
                return true;
            }
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $sender->getLevel()->getName() . ' banitem ' . $data);
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Ban Items");
        $form->addInput("Please enter the item ID (number) to be banned", "Ex. Diamond (264)");
        $form->sendToPlayer($sender);
        return $form;
    }

    /**
     * Disables a command
     *
     * @return void
     */
    public function banCommand($sender) {
        $form = new CustomForm(function (Player $sender, $data) {
            if ($data === null) {
                return true;
            }
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $sender->getLevel()->getName() . ' bancmd ' . $data);
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Ban Commands");
        $form->addInput("Please enter the command to be disabled", "Ex. VIP command(v)");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function PlayerMax($sender) {
        $form = new CustomForm(function (Player $sender, $data) {
            if ($data === null) {
                return true;
            }
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), 'wp ' . $sender->getLevel()->getName() . ' max ' . $data);
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Max Players");
        $form->addInput("Please enter the maximum number of players", "Ex. 10 players (10)");
        $form->sendToPlayer($sender);
        return $form;
    }

    public function sendCommand(Player $sender): bool {
        $form = new SimpleForm(function (Player $player, $data) {
            $result = $data;
            if ($result == null) {

            }
            switch ($result) {
                case 0:
                    $this->banCommand($player);
                    break;
                case 1:
                    $this->unbanCommand($player);
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle(TextFormat::BOLD . TextFormat::BLUE . "Island Management > Commands Management");
        $form->addButton("§l§9禁用指令",0,"textures/ui/icon_setting");
        $form->addButton("§l§9解禁指令",0,"textures/ui/icon_setting");
        $form->addButton("§c關閉",0,"textures/ui/cancel");
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
            $this->unbanItem($player);
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
            $this->sendPvP($player);
            break;

            case 2:
            $this->sendCommand($player);
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


    public function onDisable() {

    }

}
