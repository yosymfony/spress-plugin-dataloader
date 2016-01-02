<?php

namespace SpressPlugins\SpressDataLoader;

use Yosymfony\Spress\Core\Plugin\PluginInterface;
use Yosymfony\Spress\Core\Plugin\EventSubscriber;
use Yosymfony\Spress\Core\Plugin\Event\EnvironmentEvent;

class SpressDataLoader implements PluginInterface
{
    public function initialize(EventSubscriber $subscriber)
    {
        $subscriber->addEventListener('spress.start', 'onStart');
    }

    public function getMetas()
    {
        return [
            'name' => 'yosymfony/spress-plugin-dataloader',
            'description' => 'Dataloader plugin for Spress',
            'author' => 'Victor Puertas',
            'license' => 'MIT',
        ];
    }

    public function onStart(EnvironmentEvent $event)
    {
        $configValues = $event->getConfigValues();
        $dataDir = __DIR__.'/../../../data';
        $data = [];

        if (file_exists($dataDir) && is_dir($dataDir)) {
            if ($items = scandir($dataDir)) {
                foreach ($items as $item) {
                    if ($splFile = $this->getSplFile($dataDir, $item)) {
                        $data += $this->readJsonFile($splFile);
                    }
                }
            }
        }

        $configValues['data'] = $data;

        $event->setConfigValues($configValues);
    }

    private function getSplFile($source, $item)
    {
        if ($item == '.' || $item == '..') {
            return false;
        }

        $splFile = new \SplFileInfo($source.'/'.$item);
        $extension = $splFile->getExtension();

        if ($extension != 'json' || $splFile->isDir() || false == $splFile->isReadable()) {
            return false;
        }

        return $splFile;
    }

    private function readJsonFile(\SplFileInfo $splFile)
    {
        $result = [];
        $name = $splFile->getBasename('.json');
        $json = json_decode($this->getContentFile($splFile), true);
        $result[$name] = $json;

        return $result;
    }

    private function getContentFile(\SplFileInfo $splFile)
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
