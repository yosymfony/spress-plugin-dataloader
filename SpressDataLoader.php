<?php

use Symfony\Component\EventDispatcher\Event;
use Yosymfony\Spress\Plugin\Plugin;
use Yosymfony\Spress\Plugin\EventSubscriber;
use Yosymfony\Spress\Plugin\Event\EnviromentEvent;

namespace SpressPlugins\SpressDataLoader;

class SpressDataLoader extends Plugin
{
    public function initialize(EventSubscriber $subscriber)
    {
       $subscriber->addEventListener('spress.start', 'onStart');
    }
    
    public function onStart(EnviromentEvent $event)
    {
        $sourceDir = $event->getSourceDir();
        $repository = $event->getConfigRepository();
        $dataDir = $sourceDir . '/_data';
        $data = [];
        
        if(file_exists($dataDir) && is_dir($dataDir))
        {
            if ($items = scandir($dataDir))
            {
                foreach($items as $item)
                {
                    if($splFile = $this->getSplFile($dataDir, $item))
                    {
                        $data += $this->readJsonFile($splFile);
                    }
                }
            }
        }
        
        $repository['data'] = $data;
    }
    
    private function getSplFile($source, $item)
    {
        if($item == '.' || $item == '..')
        {
            return false;
        }
        
        $splFile = new SplFileInfo($source . '/' . $item);
        $extension = $splFile->getExtension();

        if($extension != 'json' || $splFile->isDir() || false == $splFile->isReadable())
        {
            return false;
        }
        
        return $splFile;
    }
    
    private function readJsonFile(SplFileInfo $splFile)
    {
        $result = [];
        $name = $splFile->getBasename('.json');
        $json = json_decode($this->getContentFile($splFile), true);
        $result[$name] = $json;

        return $result;
    }
    
    private function getContentFile(SplFileInfo $splFile)
    {
        $level = error_reporting(0);
        $content = file_get_contents($splFile->getRealPath());
        
        error_reporting($level);
        
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }
        
        return $content;
    }
}