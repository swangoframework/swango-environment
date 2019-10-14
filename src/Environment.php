<?php
namespace Swango;
abstract class Environment {
    private static $storage = [];
    protected static $default_config, $basic_config;
    public static function setBasicConfigFile(string $config_file): void {
        if (! file_exists($config_file))
            throw new Environment\Exception('Config file not exists');
        try {
            $basic_config = \Json::decodeAsObject(file_get_contents($config_file));
        } catch(\JsonDecodeFailException $e) {
            throw new Environment\Exception('Config file json decode fail');
        }
        if (! is_object($basic_config))
            throw new Environment\Exception('Config file not an object');
        self::$basic_config = $basic_config;
    }
    protected static function getDefaultConfig(): \stdClass {
        if (self::$default_config === null) {
            $default_config = \Json::decodeAsObject(file_get_contents(__DIR__ . '/../default/swango.json'));
            $unset = [];
            foreach ($default_config as $k=>$v)
                if ($k{0} === '_')
                    $unset[] = $k;
            foreach ($unset as $k)
                unset($default_config->{$k});
            self::$default_config = $default_config;
        }
        return self::$default_config;
    }
    public static function getDir(): \Swango\Environment\Dir {
        if (! array_key_exists('Dir', self::$storage)) {
            self::$storage['Dir'] = new \Swango\Environment\Dir();
        }
        return self::$storage['Dir'];
    }
    public static function getWorkingMode(): \Swango\Environment\WorkingMode {
        if (! array_key_exists('WorkingMode', self::$storage)) {
            self::$storage['WorkingMode'] = new \Swango\Environment\WorkingMode();
        }
        return self::$storage['WorkingMode'];
    }
    public static function getServiceConfig(): \Swango\Environment\Service {
        if (! array_key_exists('Service', self::$storage)) {
            self::$storage['Service'] = new \Swango\Environment\Service();
        }
        return self::$storage['Service'];
    }
    public static function getName(): string {
        if (! array_key_exists('Name', self::$storage)) {
            if (self::$basic_config === null || ! self::$basic_config->name) {
                $name = self::getDefaultConfig()->name;
            } else {
                $name = self::$basic_config->name;
            }
            self::$storage['Name'] = $name;
        }
        return self::$storage['Name'];
    }
    public static function getFrameworkConfig(string $category): array {
        if (self::$basic_config === null || ! isset(self::$basic_config->framwork->{$category})) {
            $config = self::getDefaultConfig()->framwork->{$category};
        } else {
            $config = self::$basic_config->framwork->{$category};
        }
        if (is_string($config) && '' !== $config) {
            $file = self::getDir()->getParsedDir($config);
            if (file_exists($file))
                return \Json::decodeAsArray(file_get_contents($file));
            else
                throw new Environment\Exception('Cannot find config file for ' . $category . ' in ' . $file);
        }
        if (is_object($config))
            return (array)$config;
        throw new Environment\Exception('Cannot find config for ' . $category);
    }
    public static function getConfig(string $key): array {
        $file = self::getDir()->config . "$key.json";
        if (! file_exists($file))
            throw new Environment\Exception('Cannot find config file for ' . $key . ' in ' . $file);
        return \Json::decodeAsArray($file);
    }
    public function __set(string $key, $value) {
        trigger_error('Do not try to set property');
    }
    public function __unset(string $key) {
        trigger_error('Do not try to unset property');
    }
}