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
    public static function getLocalIp(): string {
        if (! array_key_exists('LocalIp', self::$storage)) {
            if (self::$basic_config === null || ! property_exists(self::$basic_config, 'localip')) {
                $localip = self::getDefaultConfig()->localip;
            } else {
                $localip = self::$basic_config->localip;
            }
            $ip = null;
            if (is_string($localip) && '' !== $localip) {
                if (filter_var($localip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ip = $localip;
                } else {
                    $file = self::getDir()->getParsedDir($localip);
                    if (file_exists($file)) {
                        $ip = file_get_contents($file);
                        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                            trigger_error('Cannot read local ip from file ' . $file);
                    } else {
                        $net_card_data = swoole_get_local_ip();
                        if (array_key_exists($localip, $net_card_data))
                            $ip = $net_card_data[$localip];
                        else
                            trigger_error('Cannot define ip by ' . $localip);
                    }
                }
            }
            if (! isset($ip))
                $ip = swoole_get_local_ip()['eth0'];
            self::$storage['LocalIp'] = $ip;
        }
        return self::$storage['LocalIp'];
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