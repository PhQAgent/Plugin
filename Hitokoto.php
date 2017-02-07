<?php
namespace plugin;

use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\console\MainLogger;
use phqagent\utils\Curl;

class Hitokoto extends PluginBase {
    /* array */
    private static $hitoData;
    /* int */
    private $hitoCount;

    public function onLoad()
    {
        $this->initHito();
        MainLogger::success('一言[Hitokoto]加载成功!');
        MainLogger::warning($this->getHito());
    }

    private function initHito()
    {
        $path = $this->getDataDir();
        if (!file_exists($path . 'hitokoto.json')) {
            $curl = new Curl();
            $json = $curl->
            setUrl('http://hitokoto.api.bilibibi.me/hitokoto.json')->
            returnHeader(false)->
            exec();
            if ($json) {
                @file_put_contents($path . 'hitokoto.json', $json);
                MainLogger::success('成功获取并缓存网络Hitokoto！当前网络来源[bilibibi.me]');
            }else {
                MainLogger::warning('获取网络Hitokoto出错，请检查网络连接！');
                return false;
            }
        }else {
            $json = file_get_contents($path . 'hitokoto.json');
        }
        self::$hitoData = json_decode(urldecode($json), true);
        $this->hitoCount = self::$hitoData['num'];
    }

    private function getHito()
    {   
        $rand = mt_rand(0, $this->hitoCount);
        return (self::$hitoData[$rand]['text'] . '——' . self::$hitoData[$rand]['source']);
    }

    public function onMessageReceive(Message $msg)
    {
        if (strstr($msg->getContent(), '!hito')) {
            new Message($msg, '@' . $msg->getUser()->getCard() . ' ' . $this->getHito(), true);
        }
    }

}