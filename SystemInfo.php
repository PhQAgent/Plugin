<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\console\MainLogger;
use phqagent\message\Message;

class SystemInfo extends PluginBase{

    private $start;

    public function onLoad(){
        $this->start = time();
        MainLogger::success('系统信息插件已加载');
    }

    public function onMessageReceive(Message $message){
        if($message->getContent() == '/sysinfo'){
            $version = 'PhQAgent Codename: [' . \phqagent\PROJECT . '] Version: ' . \phqagent\VERSION . "\n";
            $uptime = "当前已运行 " . (int)(time() - $this->start) . " 秒\n";
            $plugins = "当前已加载的插件: ";
            foreach($this->getServer()->getPluginManager()->getPlugin() as $name => $p){
                $plugins .= $name . " ";
            }
            $plugins .= "\n";
            $text = $version . $uptime . $plugins;
            new Message($message, $text, true);
        }
    }

}