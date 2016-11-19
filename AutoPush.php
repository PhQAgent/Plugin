<?php
namespace plugin;

use phqagent\plugin\PluginBase;
use phqagent\console\MainLogger;
use phqagent\message\Message;
use phqagent\element\GroupList;

class AutoPush extends PluginBase{

    const URL = 'http://www.mikuclub.cn/feed';
    const GROUP = [145835874];
    private $time = 0;
    private $last = '';
    private $cache;

    public function onLoad(){
        if(file_exists($this->getDataDir() . 'last')){
            $this->last = file_get_contents($this->getDataDir() . 'last');
            MainLogger::info('载入最后的推送' . $this->last);
        }
        $this->time = time();
        $this->registerCallBack();
        MainLogger::success('WordPress自动推送插件已加载');
    }

    public function onShutdown(){
        file_put_contents($this->getDataDir() . 'last', $this->last);
    }

    public function onCallback($type){
        $diff = time() - $this->time;
        if($diff > 120){
            $this->time = time();
            MainLogger::info('检查RSS是否有更新...');
            $data = file_get_contents(self::URL);
            $xml = simplexml_load_string($data, null, LIBXML_NOCDATA);
            if(isset($xml->channel->item[0])){
                $last = $xml->channel->item[0];
                $link = (string)$last->link;
                if($link !== $this->last){
                    $this->last = $link;
                    $this->push($last);
                }
            }
        }
    }

    private function push($last){
        if(!isset($this->cache)){
            $this->cache = [];
            foreach(self::GROUP as $gc){
                $group = GroupList::getGroupList($gc);
                foreach($group as $g){
                    if($g->getNumber() == $gc){
                        $this->cache[] = $g;
                        break;
                    }
                }
            }
        }
        foreach($this->cache as $group){
            $text = "新的内容发布啦!\n";
            if(is_array($last->category)){
                $text .= "分类: \n";
                foreach($last->category as $c){
                    $text .= $c . " | ";
                }
                $text .= "\n";
            }else{
                $text .= "分类: \n" . $last->category . "\n";
            }
            $text .= "标题: \n" . $last->title . "\n";
            $text .= "链接: \n" . $last->link;
            new Message($group, $text, true);
        }

    }

}