<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\console\MainLogger;
use phqagent\message\Message;
use phqagent\element\Group;

class AutoBan extends PluginBase{

    const RANGE = 10;
    const LIMIT = 8;
    private $log = [];
    private $time = 0;

    public function onLoad(){
        $this->time = time();
        MainLogger::success('自动禁言插件已加载');
    }

    public function onMessageReceive(Message $message){
        if($message->getType() == Message::GROUP){
            $group = $message->getFrom();
            if($group->getPermission() >= Group::MANAGE){
                $this->logMessage($message);
                $this->processAlert($message);
            }
        }
        if((time() - $this->time) > self::RANGE){
            $this->log = [];
            MainLogger::info('统计时间重置');
            $this->time = time();
        }
        if($message->getContent() == '/ban me'){
            $group = $message->getFrom();
            $user = $message->getSend();
            $group->banMember($user, 60);
        }
    }

    private function logMessage($msg){
        $group = $msg->getFrom();
        $user = $msg->getSend();
        if(!isset($this->log[$group->getUin()][$user->getUin()])){
            $this->log[$group->getUin()][$user->getUin()] = 0;
        }
        $this->log[$group->getUin()][$user->getUin()] += 1;
    }

    private function processAlert($msg){
        $group = $msg->getFrom();
        $user = $msg->getSend();
        $count = $this->log[$group->getUin()][$user->getUin()];
        if(($count / self::LIMIT) > 0.8 && ($count < self::LIMIT)){
            $text = $user->getCard() . ' 您已经触发刷屏自动禁言80%阈值, 继续刷屏将导致禁言!';
            new Message($msg, $text, true);
            return ;
        }
        if($count >= self::LIMIT){
            $text = $user->getCard() . ' 您已经触发刷屏自动禁言!';
            new Message($msg, $text, true);
            $group->banMember($user, 60);
            return ;
        }
    }
    

}