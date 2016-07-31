<?php
namespace plugin;
use plugin\PluginBase;
use element\Message;
use element\ReplyMessage;
use worker\MessageSender;

class Roll extends PluginBase{

    public function onLoad(){
        $this->getServer()->getLogger()->info('roll骰子插件已加载!');
    }

    private function getRandom($arg){
        $min = 1;
        $max = is_numeric($arg) ? $arg < 1 ? 1 : $arg : 100;
        return mt_rand($min, $max);
    }

    public function onReceive(Message $message){
        if(strstr($message->getContent(), '!roll')){
            $arg = explode('!roll ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $random = $this->getRandom($arg);
            $nick = $message->getUser()->getNick($message->getGroup());
            new MessageSender(
                (new ReplyMessage($message))->setContent("@$nick rolled $random point(s)")
            );
            $this->getServer()->getLogger()->info("$nick rolled $random point(s)");
        }
    }

}