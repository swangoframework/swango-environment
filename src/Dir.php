<?php
namespace Swango\Environment;
/**
 *
 * @author fdrea
 *
 * @property string $base
 * @property string $main
 * @property string $controller
 * @property string $model
 * @property string $model_cache
 * @property string $library
 * @property string $app
 * @property string $module
 * @property string $data
 * @property string $cli
 * @property string $log
 * @property string $config
 *
 */
class Dir extends \Swango\Environment {
    private $data, $search, $replace;
    protected function __construct() {
        $this->data = new \stdClass();
        if (self::$basic_config === null || ! is_object(self::$basic_config->dir))
            $dir_config = new \stdClass();
        else
            $dir_config = self::$basic_config->dir;
        $default_config = self::getDefaultConfig();

        if (isset($dir_config->base) && '' !== $dir_config->base) {
            $base_dir = $dir_config->base;
        } else {
            $dir = __DIR__;
            $pos = strrpos($dir, 'vendor/swango/environment/src');
            $base_dir = substr($dir, 0, $pos);
        }
        $this->data->base = $base_dir;
        $search = [
            '{base}'
        ];
        $replace = [
            $base_dir
        ];
        foreach ([
            'main',
            'controller',
            'model',
            'model_cache',
            'library',
            'app',
            'module',
            'data',
            'cli',
            'config'
        ] as $key) {
            if (! isset($dir_config->{$key}) || '' === $dir_config->{$key}) {
                $dir = $default_config->dir->{$key};
            } else {
                $dir = $dir_config->{$key};
            }
            $dir = str_replace($search, $replace, $dir);
            if ($dir{- 1} !== '/')
                $dir .= '/';
            $search[] = '{' . $key . '}';
            $replace[] = $dir;
            $this->data->{$key} = $dir;
        }
        $this->search = $search;
        $this->replace = $replace;
    }
    public function __get(string $key) {
        return property_exists($this->data, $key) ? $this->data->{$key} : null;
    }
    public function __isset(string $key) {
        return isset($this->data->{$key});
    }
    public function getParsedDir(string $dir): string {
        return str_replace($this->search, $this->replace, $dir);
    }
}
