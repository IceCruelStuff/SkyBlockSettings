<?php
namespace aliuly\worldprotect;

//= cmd:add,Sub_Commands
//: Add player to the authorized list
//> usage: /wp _[world]_ **add** _<player>_
//= cmd:rm,Sub_Commands
//: Removes player from the authorized list
//> usage: /wp _[world]_ **rm** _<player>_
//=  cmd:unlock,Sub_Commands
//: Removes protection
//> usage: /wp _[world]_ **unlock**
//= cmd:lock,Sub_Commands
//: Locks world, not even Op can use.
//> usage: /wp _[world]_ **lock**
//= cmd:protect,Sub_Commands
//: Protects world, only certain players can build.
//> usage: /wp _[world]_ **protect**
//:
//: When in this mode, only players in the _authorized_ list can build.
//: If there is no authorized list, it will use **wp.cmd.protect.auth**
//: permission instead.
//:
//= features
//: * Protect worlds from building/block breaking
//
//= docs
//: This plugin protects worlds from griefers by restricing placing and breaking
//: blocks.  Worlds have three protection levels:
//:
//: * unlock - anybody can place/break blocks
//: * protect - players in the _authorized_ list or, if the list is empty,
//:   players with **wp.cmd.protect.auth** permission can place/break
//:   blocks.
//: * lock - nobody (even *ops*) is allowed to place/break blocks.
//:
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use aliuly\worldprotect\common\mc;
class WpProtectMgr extends BaseWp implements Listener
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
        $this->enableSCmd("add", ["usage" => mc::_("<user>"), "help" => mc::_("添加 <用户> 到授权列表"), "permission" => "wp.cmd.addrm"]);
        $this->enableSCmd("rm", ["usage" => mc::_("<user>"), "help" => mc::_("移除 <用户> 从授权列表"), "permission" => "wp.cmd.addrm"]);
        $this->enableSCmd("unlock", ["usage" => "", "help" => mc::_("取消世界保护"), "permission" => "wp.cmd.protect", "aliases" => ["unprotect", "open"]]);
        $this->enableSCmd("lock", ["usage" => "", "help" => mc::_("封锁\n没人 (包括OP) 能在这世界建筑"), "permission" => "wp.cmd.protect"]);
        $this->enableSCmd("protect", ["usage" => "", "help" => mc::_("只有被授权的用户 (或OP) 可以建筑"), "permission" => "wp.cmd.protect"]);
    }
    public function onSCommand(CommandSender $c, Command $cc, $scmd, $world, array $args)
    {
        switch ($scmd) {
            case "add":
                if (!count($args)) {
                    return false;
                }
                foreach ($args as $i) {
                    $player = $this->owner->getServer()->getPlayer($i);
                    if (!$player) {
                        $player = $this->owner->getServer()->getOfflinePlayer($i);
                        if ($player == null || !$player->hasPlayedBefore()) {
                            $c->sendMessage(mc::_("[WP] %1%: 没找到", $i));
                            continue;
                        }
                    }
                    $iusr = strtolower($player->getName());
                    $this->owner->authAdd($world, $iusr);
                    $c->sendMessage(mc::_("[WP] %1% 已被添加到 %2% 世界的授权列表", $i, $world));
                    if ($player instanceof Player) {
                        $player->sendMessage(mc::_("[WP] 你已经被加入到\n[WP] %1% 世界的授权列表", $world));
                    }
                }
                return true;
            case "rm":
                if (!count($args)) {
                    return false;
                }
                foreach ($args as $i) {
                    $iusr = strtolower($i);
                    if ($this->owner->authCheck($world, $iusr)) {
                        $this->owner->authRm($world, $iusr);
                        $c->sendMessage(mc::_("[WP] %1% 已从 %2% 世界的授权列表移除", $i, $world));
                        $player = $this->owner->getServer()->getPlayer($i);
                        if ($player) {
                            $player->sendMessage(mc::_("[WP] 你已经从\n[WP] %1% 世界的授权列表移除", $world));
                        }
                    } else {
                        $c->sendMessage(mc::_("[WP] %1% 世界不明", $i));
                    }
                }
                return true;
            case "unlock":
                if (count($args)) {
                    return false;
                }
                $this->owner->unsetCfg($world, "protect");
                $this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% 世界已经被开放", $world));
                return true;
            case "lock":
                if (count($args)) {
                    return false;
                }
                $this->owner->setCfg($world, "protect", $scmd);
                $this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% 世界已经被封锁", $world));
                return true;
            case "protect":
                if (count($args)) {
                    return false;
                }
                $this->owner->setCfg($world, "protect", $scmd);
                $this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% 世界已经被保护", $world));
                return true;
        }
        return false;
    }
    protected function checkBlockPlaceBreak(Player $p)
    {
        $world = $p->getLevel()->getName();
        if (!isset($this->wcfg[$world])) {
            return true;
        }
        if ($this->wcfg[$world] != "protect") {
            return false;
        }
        // LOCKED!
        return $this->owner->canPlaceBreakBlock($p, $world);
    }
    public function onBlockBreak(BlockBreakEvent $ev)
    {
        if ($ev->isCancelled()) {
            return;
        }
        $pl = $ev->getPlayer();
        if ($this->checkBlockPlaceBreak($pl)) {
            return;
        }
        $pk = new \pocketmine\network\mcpe\protocol\LevelSoundEventPacket();
        $pk->sound = 1;
        $pk->position = new \pocketmine\math\Vector3($ev->getBlock()->x, $ev->getBlock()->y, $ev->getBlock()->z);
        $pk->extraData = 0;
        $pk->entityType = "0";
        $pk->isBabyMob = true;
        $pk->disableRelativeVolume = true;
        $ev->getPlayer()->dataPacket($pk);
        $this->owner->msg($pl, mc::_("你不能在這裡做到這事"));
        $ev->setCancelled();
    }
    public function onBlockPlace(BlockPlaceEvent $ev)
    {
        if ($ev->isCancelled()) {
            return;
        }
        $pl = $ev->getPlayer();
        if ($this->checkBlockPlaceBreak($pl)) {
            return;
        }
        $pk = new \pocketmine\network\mcpe\protocol\LevelSoundEventPacket();
        $pk->sound = 1;
        $pk->position = new \pocketmine\math\Vector3($ev->getBlock()->x, $ev->getBlock()->y, $ev->getBlock()->z);
        $pk->extraData = 0;
        $pk->entityType = "0";
        $pk->isBabyMob = true;
        $pk->disableRelativeVolume = true;
        $ev->getPlayer()->dataPacket($pk);
        $this->owner->msg($pl, mc::_("你不能在這裡做到這事"));
        $ev->setCancelled();
    }
}