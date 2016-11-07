<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\console\MainLogger;

class Repeat extends PluginBase{

    private $data;

    public function onLoad(){
        MainLogger::info('自动复读插件已加载');
        $this->data = [];
    }

    public function onMessageReceive(Message $message){
        if($message->getType() == Message::GROUP){
            if(substr($message->getContent(), 0, 1) == '!'){
                return ;
            }
            $gid = $message->getGroup()->getGid();
            $msg = $message->getContent();
            if(!isset($this->data[$gid])){
                $this->data[$gid] = [];
            }
            if(count($this->data[$gid]) >= 10){
                $key = current($this->data[$gid])[1];
                unset($this->data[$gid][$key]);
            }
            if(!isset($this->data[$gid][$msg])){
                $this->data[$gid][$msg] = [0, $msg];
            }
            $this->data[$gid][$msg][0]++;
            if($this->data[$gid][$msg][0] >= 3){
                unset($this->data[$gid][$msg]);
                new Message($message, $message->getContent(), true);
            }
        }
    }

}