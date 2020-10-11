<?php
//= cmd:unbreakable|breakable,Sub_Commands
//: Control blocks that can/cannot be broken
//> usage: /wp  _[world]_ **breakable|unbreakable** _[block-ids]_
//:
//: Manages which blocks can or can not be broken in a given world.
//: You can get a list of blocks currently set to **unbreakable**
//: if you do not specify any _[block-ids]_.  Otherwise these are
//: added or removed from the list.
//:
//= features
//: * Unbreakable blocks
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use aliuly\worldprotect\common\mc;
use aliuly\worldprotect\common\ItemName;
class Unbreakable extends BaseWp implements Listener
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
        $this->enableSCmd("unbreakable", ["usage" => mc::_("[id] [id]"), "help" => mc::_("設定某方塊為不能破壞狀態"), "permission" => "wp.cmd.unbreakable", "aliases" => ["ubab"]]);
        $this->enableSCmd("breakable", ["usage" => mc::_("[id] [id]"), "help" => mc::_("移除某方塊中的不能破壞狀態"), "permission" => "wp.cmd.unbreakable", "aliases" => ["bab"]]);
    }
    public function onSCommand(CommandSender $c, Command $cc, $scmd, $world, array $args)
    {
        if ($scmd != "breakable" && $scmd != "unbreakable") {
            return false;
        }
        if (count($args) == 0) {
            $ids = $this->owner->getCfg($world, "unbreakable", []);
            if (count($ids) == 0) {
                $c->sendMessage(mc::_("[WP] 在 %1% 世界中沒有不能破壞的方塊", $world));
            } else {
                $ln = mc::_("[WP] 方塊(%1%):", count($ids));
                $q = "";
                foreach ($ids as $id => $n) {
                    $ln .= "{$q} {$n}({$id})";
                    $q = ",";
                }
                $c->sendMessage($ln);
            }
            return true;
        }
        $cc = 0;
        $ids = $this->owner->getCfg($world, "unbreakable", []);
        if ($scmd == "breakable") {
            foreach ($args as $i) {
                $item = Item::fromString($i);
                if (isset($ids[$item->getId()])) {
                    unset($ids[$item->getId()]);
                    ++$cc;
                }
            }
        } elseif ($scmd == "unbreakable") {
            foreach ($args as $i) {
                $item = Item::fromString($i);
                if (isset($ids[$item->getId()])) {
                    continue;
                }
                $ids[$item->getId()] = ItemName::str($item);
                ++$cc;
            }
        } else {
            return false;
        }
        if (!$cc) {
            $c->sendMessage(mc::_("沒有方塊更新"));
            return true;
        }
        if (count($ids)) {
            $this->owner->setCfg($world, "unbreakable", $ids);
        } else {
            $this->owner->unsetCfg($world, "unbreakable");
        }
        $c->sendMessage(mc::_("方塊改變: %1%", $cc));
        return true;
    }
    public function onBlockBreak(BlockBreakEvent $ev)
    {
        if ($ev->isCancelled()) {
            return;
        }
        $bl = $ev->getBlock();
        $world = $bl->getLevel()->getName();
        if (!isset($this->wcfg[$world])) {
            return;
        }
        if (!isset($this->wcfg[$world][$bl->getId()])) {
            return;
        }
        $pl = $ev->getPlayer();
        $pl->sendMessage(mc::_("§c!!! 你無法在本世界破壞此方塊 !!!"));
        $ev->setCancelled();
    }
}