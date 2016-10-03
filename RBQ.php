<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\console\MainLogger;

class RBQ extends PluginBase{

    private $conf;

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

    public function onLoad(){
        $this->conf = $this->getDataDir('RBQ').'rbq.json';
        if(!file_exists($this->conf)){
            MainLogger::warning('初始化配置数据库');
            $this->saveDB();
        }else{
            MainLogger::info('载入数据库中……');
            $this->loadDB();
        }
        MainLogger::info('随机RBQ插件已加载!');
    }

    private function saveDB(){
        file_put_contents($this->conf, json_encode($this->type));
    }

    private function loadDB(){
        $db = json_decode(file_get_contents($this->conf), 1);
        if(!is_array($db)){
            MainLogger::alert('数据损坏');
            return;
        }
        $this->type = $db;
    }

    private function writelog($message){
        if($message->getType() == Message::GROUP){
            if(!isset($this->map[$message->getGroup()->getUin()])){
                $this->map[$message->getGroup()->getUin()] = [];
            }
            
            foreach($this->map[$message->getGroup()->getUin()] as $user){
                if($user == $message->getUser()->getCard()){
                    return true;
                }
            }
            
            if(count($this->map[$message->getGroup()->getUin()]) < 10){
                $this->map[$message->getGroup()->getUin()][] = $message->getUser()->getCard();
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
        $nick = $message->getUser()->getCard();
        if($rbq !== $nick){
            return "$nick 获得了一个 {$this->getRBQType()} 的 $rbq";
        }else{
            return "$nick 脸太黑，只能当别人的RBQ";
        }
        
    }

    public function onMessageReceive(Message $message){
        $this->writelog($message);
        if(strstr($message->getContent(), '!rbqtype')){
            $arg = explode('!rbqtype ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            if(trim($arg) !== ''){
                if(!in_array($arg, $this->type)){
                    $this->type[] = $arg;
                    $this->saveDB();
                    $msg = "已添加RBQ类型: $arg";
                }else{
                    $msg = "RBQ类型: $arg 已存在";
                }
                MainLogger::info($message->getUser->getCard() . ' : ' . $message);
                new Message($message, $msg, true);
            }else{
                $this->send((new ReplyMessage($message))->setContent("添加RBQ类型用法: !rbqtype 类型"));
            }
            return;
        }
        
        if(strstr($message->getContent(), '!rbq')){
            $arg = explode('!rbq ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $msg = $this->getRBQ($message);
            MainLogger::info($message->getUser()->getCard() . ' : ' . $message);
            new Message($message, $msg, true);
            return;
        }
        
    }

}