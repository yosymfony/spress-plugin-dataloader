<?php

namespace SpressPlugins\SpressDataLoader;

use Symfony\Component\Yaml\Exception\ParseException;
use Yosymfony\Spress\Core\Plugin\PluginInterface;
use Yosymfony\Spress\Core\Plugin\EventSubscriber;
use Yosymfony\Spress\Core\Plugin\Event\EnvironmentEvent;
use Symfony\Component\Yaml\Yaml;
use Michelf\MarkdownExtra;

class SpressDataLoader implements PluginInterface
{
    const EXTENSIONS_YAML = [ 'yml', 'yaml', ];
    const EXTENSIONS_JSON = [ 'json', ];
    const EXTENSIONS_MARKDOWN = [ 'md', 'markdown' ];
    const EXTENSIONS_TEXT = [ 'text', 'txt' ];

    const EXTENSION_MAPPING = [
        'readYamlFile' => self::EXTENSIONS_YAML,
        'readJsonFile' => self::EXTENSIONS_JSON,
        'readMarkdownFile' => self::EXTENSIONS_MARKDOWN,
        'readTextFile' => self::EXTENSIONS_TEXT,
    ];

    /** @var string[] */
    private $extensions = [];

    /** @var string */
    private $dataDir;

    public function __construct()
    {
        foreach (self::EXTENSION_MAPPING as $extensions) {
            foreach ($extensions as $extension) {
                $this->extensions[] = $extension;
            }
        }
        $this->dataDir = __DIR__.'/../../../data';
    }


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
        $data = [];

        if (file_exists($this->dataDir) && is_dir($this->dataDir)) {
            if ($items = scandir($this->dataDir)) {
                foreach ($items as $item) {
                    if ($splFile = $this->getSplFile($this->dataDir, $item)) {
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

        if (false === $splFile->isReadable() || $splFile->isDir() || !in_array(strtolower($extension), $this->extensions,true)) {
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
        foreach (self::EXTENSION_MAPPING as $method => $extensions) {
            if (in_array(strtolower($splFile->getExtension()), $extensions , true)) {
                $result = [];
                $name = $splFile->getBasename('.' . $splFile->getExtension());
                $result[$name] = $this->$method($splFile);
                return $result;
            }
        }

        throw new \RuntimeException(sprintf('Extension \'%s\' is not supported.', $splFile->getExtension()));
    }

    /**
     * @param \SplFileInfo $splFile
     * @return mixed
     * @throws \RuntimeException
     */
    private function readYamlFile(\SplFileInfo $splFile)
    {
        try {
            return Yaml::parse($this->getContentFile($splFile));
        } catch (ParseException $e) {
            throw new \RuntimeException('Can\'t parse data file ' . $splFile->getBasename(), 0, $e);
        }
    }

    /**
     * @param \SplFileInfo $splFile
     * @return mixed
     */
    private function readJsonFile(\SplFileInfo $splFile)
    {
        return json_decode($this->getContentFile($splFile), true);

    }

    /**
     * @param \SplFileInfo $splFile
     * @return string
     */
    private function readMarkdownFile(\SplFileInfo $splFile)
    {
        return MarkdownExtra::defaultTransform($this->getContentFile($splFile));
    }

    /**
     * @param \SplFileInfo $splFile
     * @return string
     */
    private function readTextFile(\SplFileInfo $splFile)
    {
        return $this->getContentFile($splFile);
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
