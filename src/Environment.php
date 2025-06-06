<?php
namespace Swango;
abstract class Environment {
    const SWANGO_LOGO = <<<TEXT
     ____
    / ___|_      ____ _ _ __   __ _  ___
    \___ \ \ /\ / / _` | '_ \ / _` |/ _ \
     ___) \ V  V / (_| | | | | (_| | (_) |
    |____/ \_/\_/ \__,_|_| |_|\__, |\___/
                              |___/
    TEXT;
    private static $storage = [];
    protected static $default_config, $basic_config, $config = [];
    public static function setBasicConfigFile(string $config_file): void {
        if (! file_exists($config_file)) {
            throw new Environment\Exception('Config file not exists');
        }
        $basic_config = json_decode(file_get_contents($config_file), false);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Environment\Exception('Config file json decode fail');
        }
        if (! is_object($basic_config)) {
            throw new Environment\Exception('Config file not an object');
        }
        self::$basic_config = $basic_config;
    }
    protected static function getDefaultConfig(): \stdClass {
        if (self::$default_config === null) {
            $default_config = json_decode(file_get_contents(__DIR__ . '/../default/swango.json'), false);
            $unset = [];
            foreach ($default_config as $k => $v)
                if ($k[0] === '_') {
                    $unset[] = $k;
                }
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
    public static function getSwangoModuleSeeker(): \Swango\Environment\SwangoModuleSeeker {
        if (! array_key_exists('SwangoModuleSeeker', self::$storage)) {
            self::$storage['SwangoModuleSeeker'] = new \Swango\Environment\SwangoModuleSeeker();
        }
        return self::$storage['SwangoModuleSeeker'];
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
            $config_obj = self::getDefaultConfig();
        } else {
            $config_obj = self::$basic_config;
        }
        $config = $config_obj->framwork->{$category};
        if (is_string($config) && '' !== $config) {
            $file = self::getDir()->getParsedDir($config);
            if (file_exists($file)) {
                $ret = json_decode(file_get_contents($file), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Environment\Exception('Config file json decode fail');
                }
                $config_obj->framwork->{$category} = $ret;
                return $ret;
            } else {
                throw new Environment\Exception('Cannot find config file for ' . $category . ' in ' . $file);
            }
        }
        if (is_object($config)) {
            $config = (array)$config;
        }
        return $config;
    }
    public static function getConfig(string $key): array {
        if (! array_key_exists($key, self::$config)) {
            $file = self::getDir()->config . "$key.json";
            if (! file_exists($file)) {
                throw new Environment\Exception('Cannot find config file for ' . $key . ' in ' . $file);
            }
            $ret = json_decode(file_get_contents($file), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Environment\Exception('Config file json decode fail');
            }
            self::$config[$key] = $ret;
            return $ret;
        } else {
            return self::$config[$key];
        }
    }
    public function __set(string $key, $value) {
        trigger_error('Do not try to set property on Environment object');
    }
    public function __unset(string $key) {
        trigger_error('Do not try to unset property on Environment object');
    }
}
