<?php
use plugin\PluginBase;
class MP extends PluginBase{

    public function onReceive(){
        if(strstr($this->getMessage(), '!roll')){
            $arg = explode('!roll ', $this->getMessage());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $this->reply("@{$this->getNickName()} rolled {$this->getRandom($arg)} point(s)");
        }
    }

}