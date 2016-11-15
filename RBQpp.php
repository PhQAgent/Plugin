<?php
namespace plugin;
use phqagent\Server;
use phqagent\plugin\PluginBase;
use phqagent\console\MainLogger;
use phqagent\message\Message;

class RBQpp extends PluginBase{

    private $log = [];
    private $block = [];

    public function onLoad(){
        if(version_compare("2.3.3", \phqagent\VERSION) > 0){
            MainLogger::alert("请升级PhQAgent框架到最新版!");
            return false;
        }
        MainLogger::success('全新的RBQ++插件加载完成啦!');
    }

    public function onMessageReceive(Message $message){
        if($message->getType() == Message::GROUP){
            if($message->getContent() == "!rbq"){
                $this->processRBQ($message);
            }elseif($message->getContent() == "!block"){
                $this->processBlock($message);
            }
            $this->logMessage($message);
        }

    }

    private function logMessage($msg){
        $group = $msg->getFrom();
        $user = $msg->getSend();
        if(!isset($this->log[$group->getUin()][$user->getUin()])){
            $this->log[$group->getUin()][$user->getUin()] = $user;
        }
        if(count($this->log[$group->getUin()]) > 10){
            array_shift($this->log[$group->getUin()]);
        }
    }

    private function processRBQ($msg){
        $group = $msg->getFrom();
        $user = $msg->getSend();
        if(!isset($this->log[$group->getUin()])){
            $text = "现在还没有RBQ可供挑选, 恭喜您成为在下一次抽取100%命中的RBQ";
            new Message($msg, $text, true);
            return ;
        }
        $keys = array_keys($this->log[$group->getUin()]);
        $rbq = $keys[mt_rand(0, count($keys) - 1)];
        if($rbq === $user->getUin()){
            $text = $user->getCard() . " 脸太黑, 只能当别人的RBQ, 下一抽一定是你哦~";
            new Message($msg, $text, true);
            unset($this->log[$group->getUin()]);
            return ;
        }
        $rbq = $this->log[$group->getUin()][$rbq];
        $text = $user->getCard() . "获得了一个 " . $this->processType() . " 的 " . $rbq->getCard() . " 作为RBQ!";
        if(mt_rand(0, 100) < 5){
            $text .= "\n\n[欧皇限定] " . $rbq->getCard() . "拼命挣扎, 是否要堵住它的嘴? 发送指令 !block 禁言 " . $rbq->getCard() . " 一分钟, 该指令会在下一次获得欧皇限定时覆盖.";
            $this->block[$group->getUin()][$user->getUin()] = $rbq;
        }
        new Message($msg, $text, true);
        return ;
    }

    private function processBlock($msg){
        $group = $msg->getFrom();
        $user = $msg->getSend();
        if(isset($this->block[$group->getUin()][$user->getUin()])){
            $rbq = $this->block[$group->getUin()][$user->getUin()];
            $text = "好的! 胶布, 皮鞭, 蜡烛 ... 都准备好了哦～";
            new Message($msg, $text, true);
            $group->banMember($rbq, 60);
            return ;
        }else{
            $text = $user->getCard() . " 你根本就不是欧皇, 下一抽就决定是你了!";
            new Message($msg, $text, true);
            unset($this->log[$group->getUin()]);
            return ;
        }
    }

    private function processType(){
        return $this->type[mt_rand(0, count($this->type) - 1)];
    }

    private $type = [
            '女装',
            '抖M',
            '大JJ',
            '惊天巨乳',
            '贫乳',
            '双马尾',
            '傲娇',
            '病娇',
            '变态',
            '智障',
            '发情期',
            '扶她',
            '名器级',
            '人妻',
            '全自动',
            '吃口球'
        ];

}