<?php

namespace SpressPlugins\SpressDataLoader;

use Symfony\Component\Yaml\Exception\ParseException;
use Yosymfony\Spress\Core\Plugin\PluginInterface;
use Yosymfony\Spress\Core\Plugin\EventSubscriber;
use Yosymfony\Spress\Core\Plugin\Event\EnvironmentEvent;
use Symfony\Component\Yaml\Yaml;

class SpressDataLoader implements PluginInterface
{
    const EXTENSIONS = ['json', 'yml'];

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

    /**
     * @param EnvironmentEvent $event
     * @throws \RuntimeException
     */
    public function onStart(EnvironmentEvent $event)
    {
        $configValues = $event->getConfigValues();
        $dataDir = __DIR__.'/../../../data';
        $data = [];

        if (file_exists($dataDir) && is_dir($dataDir)) {
            if ($items = scandir($dataDir)) {
                foreach ($items as $item) {
                    if ($splFile = $this->getSplFile($dataDir, $item)) {
                        $data += $this->readFile($splFile);
                    }
                }
            }
        }

        $configValues['data'] = $data;

        $event->setConfigValues($configValues);
    }

    private function getSplFile($source, $item)
    {
        if ($item === '.' || $item === '..') {
            return false;
        }

        $splFile = new \SplFileInfo($source.'/'.$item);
        $extension = $splFile->getExtension();

        if (false === $splFile->isReadable() || $splFile->isDir() || !in_array(strtolower($extension), self::EXTENSIONS,true)) {
            return false;
        }

        return $splFile;
    }

    /**
     * @param \SplFileInfo $splFile
     * @return array
     * @throws \RuntimeException
     */
    private function readFile(\SplFileInfo $splFile)
    {
        if ('yml' === strtolower($splFile->getExtension())) {
            return $this->readFileYaml($splFile);
        }

        if ('json' === strtolower($splFile->getExtension())) {
            return $this->readJsonFile($splFile);
        }

        throw new \RuntimeException(sprintf('Extension \'%s\' is not supported.', $splFile->getExtension()));
    }

    /**
     * @param \SplFileInfo $splFile
     * @return array
     * @throws \RuntimeException
     */
    private function readFileYaml(\SplFileInfo $splFile)
    {
        $result = [];
        $name = $splFile->getBasename('.' . $splFile->getExtension());
        try {
            $data =  Yaml::parse($this->getContentFile($splFile));
        } catch (ParseException $e) {
            throw new \RuntimeException('Can\'t parse data file ' . $splFile->getBasename(), 0, $e);
        }
        $result[$name] = $data;

        return $result;
    }

    /**
     * @param \SplFileInfo $splFile
     * @return array
     */
    private function readJsonFile(\SplFileInfo $splFile)
    {
        $result = [];
        $name = $splFile->getBasename('.' . $splFile->getExtension());
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
