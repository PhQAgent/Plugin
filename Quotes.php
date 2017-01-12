<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\element\User;
use protocol\Protocol;
use phqagent\console\MainLogger;
use phqagent\console\TextFormat;

class Quotes extends PluginBase
{
    /* String */
    protected $quotePath;
    /* int Second */
    protected $saveInterval;
    /* int */
    protected $ownerUin;
    /* Array Pool */

    protected $quotePool;

    public function onLoad()
    {
        $this->initQuotes();
        MainLogger::info(TextFormat::GREEN .'Quotes语录记载插件加载成功!');
        MainLogger::info(TextFormat::YELLOW . 'Quotes自动保存间隔设定为' .($this->saveInterval / 60). '分钟！');
        //$startTime = time();
        //new Message(new User(2117587402),'于['.date("Y-m-d H:i:s",$startTime).']启动',true);//TODO::23333
        $this->registerTimeCallback(time() + $this->saveInterval);
    }

    public function initQuotes()
    {
        $this->quotePath = dirname(__FILE__).'\\Quotes';
        $this->saveInterval = 240;
    
        if(!is_dir($this->quotePath)) @mkdir($this->quotePath);
        //从硬盘中读取语录
        foreach(glob($this->quotePath . "\\*.json") as $q)
        {   
            $temp = explode('\\',$q);
            $filename = end($temp);
            $filepath = $this->quotePath.'\\'.$filename;
            $json = file_get_contents($filepath);
            $quotes = json_decode($json,true);
            $temp = explode('.',$filename);
            $name = $temp[0];
            $name = iconv("GBK", "UTF-8", $name);
            $this->quotePool[$name] = $quotes;
        }
        //print_r($this->quotePool);
        //MainLogger::info(TextFormat::YELLOW .'Quotes语录现已有玩家'.$players);
    }

    public function addPlayer($name)
    {
        if($this->hasPlayer($name)) return false;
        $content = [
            //'uid'=>$uid,
            'name'=>$name,
            'quotes'=>[]
        ];
        $this->quotePool[$name] = $content;
        //要不要触发保存呢？
        return true;
    }

    public function addQuote($name, $quote)
    {
        if(!$this->hasPlayer($name)) return false;
        $quotes = $this->quotePool[$name];
        if(in_array($quote,$this->quotePool[$name]['quotes'])) return false;
        $this->quotePool[$name]['quotes'] = array_merge($this->quotePool[$name]['quotes'],[$quote]);
        return true;
    }

    public function sayQuote($name)
    {   
        if(!$this->hasPlayer($name)) return false;
        $interest = $this->quotePool[$name]['quotes'];
        $num = count($interest);
        if($num == 0) return false;
        $ran = mt_rand(0,$num-1);
        return $interest[$ran];
    }

    public function clearQuote($name){
        //判断存在
        if(!$this->hasPlayer($name)) return false;
        unset($this->quotePool[$name]);
        $name = iconv("UTF-8", "GBK", $name);
        $filepath = $this->quotePath.'\\'.$name.'.json';
        unlink($filepath);
        $this->saveQuotes();
        return true;
    }//慎重！！！

    public function onMessageReceive(Message $msg)
    {   
        //Show User
        if(strstr($msg->getContent(),"我的信息")){
            print_r($msg->getUser());
        }
        if(strstr($msg->getContent(),"群的信息")){
            print_r($msg->getGroup());
        }
         //添加一条新的语录
         if(substr($msg->getContent(),0,2) == '+@')
         {
             $args = explode(' ',$msg->getContent());

             $name = substr($args[0],2);

            if(!isset($args[1]) || $args[1] == ''){
                new Message($msg,'缺少要添加的语录哦！',true);
                return false;
            }else{
                $quote = $args[1];

                $bool = $this->addQuote($name,$quote);
                if($bool){
                    $send = '这个新的语录['.$quote.']加入到'.$name.'的名下';//事件发生，事件内容：名词 动词
                    new Message($msg,'=￣ω￣= 好的，主人~\r\n我已经把'.$send.'了！',true);
                    MainLogger::info(TextFormat::GREEN.$send);
                }
            }
         }
         //显示语录 建立语录账号
         if(substr($msg->getContent(),0,1) == '@')
         {
            $args = explode(' ',$msg->getContent());
            if(!isset($args[1])) $args[1] = '';
            print_r($args);
            $name = ltrim($args[0],'@');
            //TODO:$name为全体成员时候
            switch($args[1])
            {
                case '':
                    $say = $this->sayQuote($name);
                    if($say)
                        new Message($msg,$name.':'.$say,true);
                    else
                        new Message($msg,'对方没有任何语录并向你扔了一个杰宝~',true);
                    break;
                case '建号':
                case '给这个家伙建个账户！':
                    $bool = $this->addPlayer($name);
                    if($bool)
                    {
                        $send = $name.'的语录账户建立成功！';
                        new Message($msg,'=￣ω￣= 好的，主人~\r\n'.$send,true);
                        MainLogger::info(TextFormat::GREEN.$send);
                    }else{}//TODO:语录账号已存在的情况 
                    break;
                case 'save': 
                case '保存':
                    $bool = $this->saveQuotes();
                    if($bool)
                    {   
                        $send = "所有的语录数据保存到硬盘";
                        new Message($msg,'=￣ω￣= 好的，主人~\r\n我已经把'.$send.'啦！',true);
                        MainLogger::info(TextFormat::GREEN.$send);
                    }
                    break;
                case '销毁':
                    $bool = $this->clearQuote($name);
                    if($bool)
                    {   
                        $send = $name.'的语录数据销毁';
                        new Message($msg,'=￣ω￣= 好的，主人~\r\n我已经把'.$send.'了！',true);
                        MainLogger::info(TextFormat::YELLOW.$send);
                    }
                    break;
            }
         }
    }

    public function onCallback($type)
    {
        $this->saveQuotes();
        MainLogger::info(TextFormat::GREEN."语录数据自动保存成功~~~");
        $this->registerTimeCallback(time() + $this->saveInterval);
    }

    public function hasPlayer($name){ return isset($this->quotePool[$name])?true:false; }
    //保存语录到硬盘
    public function saveQuotes()
    {
        foreach($this->quotePool as $name => $quotes)
        {
            $name = iconv("UTF-8", "GBK", $name);
            $filepath = $this->quotePath.'\\'.$name.'.json';
            $file = fopen($filepath, "w");
            fwrite($file, json_encode($quotes,JSON_UNESCAPED_UNICODE));
            fclose($file);
        }
        return true;
    }
    //服务器意外关闭时保存数据
    public function onShutdown()
    {
        //$endTime = time();
        //new Message(new User(2928917920),'我于['.date("Y-m-d H:i:s",$endTime).']关闭啦！',true);
        $this->saveQuotes();
    }
}
