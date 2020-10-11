<?php
//= cmd:max,Sub_Commands
//: Limits the number of players per world
//>  usage : /wp _[world]_ max _[value]_
//>   - /wp _[world]_ **max**
//:     - shows the current limit
//>   - /wp _[world]_ **max** _value_
//:     - Sets limit value to _value_.
//>   - /wp _[world]_ **max** **0**
//:     - Removes world limits
//:
//= features
//: * Limit the number of players in a world
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\Player;
use aliuly\worldprotect\common\mc;
use pocketmine\level\Level;
class MaxPlayerMgr extends BaseWp implements Listener
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct($plugin);
        $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
        $this->enableSCmd("max", ["usage" => mc::_("[value]"), "help" => mc::_("限制玩家數量\n\t於世界為 [數值]\n\t使用 0 或 -1 來移除限制"), "permission" => "wp.cmd.limit", "aliases" => ["limit"]]);
    }
    public function getMaxPlayers($world)
    {
        if ($world instanceof Level) {
            $world = $world->getName();
        }
        return $this->getCfg($world, null);
    }
    public function onSCommand(CommandSender $c, Command $cc, $scmd, $world, array $args)
    {
        if ($scmd != "max") {
            return false;
        }
        if (count($args) == 0) {
            $count = $this->owner->getCfg($world, "max-players", null);
            if ($count == null) {
                $c->sendMessage(mc::_("[WP] %1% 世界的玩家限制為： 無限", $world));
            } else {
                $c->sendMessage(mc::_("[WP] %1% 世界的玩家限制為： %2%", $world, $count));
            }
            return true;
        }
        if (count($args) != 1) {
            return false;
        }
        $count = intval($args[0]);
        if ($count <= 0) {
            $this->owner->unsetCfg($world, "max-players");
            $this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% 世界的玩家限制已被移除", $world));
        } else {
            $this->owner->setCfg($world, "max-players", $count);
            $this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% 世界的玩家限制已設定成 %2%", $world, $count));
        }
        return true;
    }
    public function onTeleport(EntityTeleportEvent $ev)
    {
        if ($ev->isCancelled()) {
            return;
        }
        $et = $ev->getEntity();
        if (!$et instanceof Player) {
            return;
        }
        $from = $ev->getFrom()->getLevel();
        $to = $ev->getTo()->getLevel();
        if (!$from) {
            // THIS SHOULDN'T HAPPEN!
            return;
        }
        if (!$to) {
            // Somebody did not initialize the level properly!
            // But we return because they do not intent to change worlds
            return;
        }
        $from = $from->getName();
        $to = $to->getName();
        if ($from == $to) {
            return;
        }
        $max = $this->getCfg($to, 0);
        if ($max == 0) {
            return;
        }
        $np = count($this->owner->getServer()->getLevelByName($to)->getPlayers());
        if($et instanceof Player && $et->isOp() && $np >= $max){
        $et->sendMessage("OP直接無視傳送XD");
        return;
        }
        if ($np >= $max) {
            $ev->setCancelled();
            $et->sendMessage(mc::_("無法傳送到世界\n世界玩家數量已滿", $to));
            $this->owner->getLogger()->notice(mc::_("%1% 世界玩家數量已滿", $to));
        }
    }
}