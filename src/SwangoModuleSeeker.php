<?php
namespace Swango\Environment;
class SwangoModuleSeeker {
    private $cache = [];
    public function swangoDbExists(): bool {
        return class_exists('\\Swango\\Db\\Exception');
    }
    public function swangoCacheExists(): bool {
        return class_exists('\\Swango\\Cache\\RedisErrorException');
    }
    public function swangoModelExists(): bool {
        return class_exists('\\Swango\\Model\\Exception\\ModelNotFoundException');
    }
    public function swangoHttpServerExists(): bool {
        return class_exists('\\Swango\\HttpServer\\Session\\Exception\\SessionNotExistsException');
    }
}