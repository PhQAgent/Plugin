<?php
namespace plugin;
use plugin\PluginBase;
use element\Message;
use element\ReplyMessage;

class RBQ extends PluginBase{

    public function onLoad(){
        $this->getServer()->getLogger()->info('随机RBQ插件已加载!');
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

    private $map;

    private function writelog($message){
        if($message->getType() == Message::GROUP){
            if(!isset($this->map[$message->getGroup()->getUin()])){
                $this->map[$message->getGroup()->getUin()] = [];
            }
            
            foreach($this->map[$message->getGroup()->getUin()] as $user){
                if($user == $message->getGroup()->getNick($message->getGroup())){
                    return true;
                }
            }
            
            if(count($this->map[$message->getGroup()->getUin()]) < 10){
                $this->map[$message->getGroup()->getUin()][] = $message->getUser()->getNick($message->getGroup());
            }elseif(count($this->map[$message->getGroup()->getUin()]) == 10){
                unset($this->map[$message->getGroup()->getUin()][0]);
                $this->map[$message->getGroup()->getUin()] = array_values($this->map[$message->getGroup()->getUin()]);
            }
            if(count($this->map[$message->getGroup()->getUin()]) > 10){
                unset($this->map[$message->getGroup()->getUin()]);
            }
        }
    }

    private function getRBQType(){
        return $this->type[mt_rand(0, count($this->type) - 1)];
    }

    private function getRBQ($message){
        $list = $this->map[$message->getGroup()->getUin()];
        $rbq = $list[mt_rand(0, count($list) - 1)];
        $nick = $message->getUser()->getNick($message->getGroup());
        if($rbq !== $nick){
            return "$nick 获得了一个 {$this->getRBQType()} 的 $rbq 作为RBQ";
        }else{
            return "$nick 脸太黑，只能当别人的RBQ";
        }
        
    }

    public function onReceive(Message $message){
        $this->writelog($message);
        if(strstr($message->getContent(), '!rbq')){
            $arg = explode('!rbq ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $msg = $this->getRBQ($message);
            $this->getServer()->getLogger()->info($msg);
            $this->send((new ReplyMessage($message))->setContent($msg));
        }
    }

}