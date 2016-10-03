<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\console\MainLogger;

class Roll extends PluginBase{

    public function onLoad(){
        MainLogger::info('roll骰子插件已加载!');
    }

    private function getRandom($arg){
        $min = 1;
        $max = is_numeric($arg) ? $arg < 1 ? 1 : $arg : 100;
        return mt_rand($min, $max);
    }

    public function onMessageReceive(Message $message){
        if(strstr($message->getContent(), '!roll')){
            MainLogger::info($message->getUser()->getCard() . ' : ' . $message);
            $arg = explode('!roll ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $random = $this->getRandom($arg);
            new Message($message, $message->getUser()->getCard() . " rolled $random point(s)", true);
        }
    }

}