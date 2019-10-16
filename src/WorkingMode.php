<?php
namespace Swango\Environment;
class WorkingMode extends \Swango\Environment {
    public const WORKING_MODE_CLI = 0;
    public const WORKING_MODE_FPM = 1;
    public const WORKING_MODE_APACHE = 2;
    public const WORKING_MODE_SWOOLE_WORKER = 3;
    public const WORKING_MODE_SWOOLE_TASK = 4;
    public const WORKING_MODE_OTHER = 5;
    private $working_mode;
    protected function __construct() {
        $this->reset();
    }
    public function getMode(): int {
        return $this->working_mode;
    }
    public function isInCliScript(): bool {
        return $this->working_mode === self::WORKING_MODE_CLI;
    }
    public function isInFpm(): bool {
        return $this->working_mode === self::WORKING_MODE_FPM;
    }
    public function isInApache(): bool {
        return $this->working_mode === self::WORKING_MODE_APACHE;
    }
    public function isInSwooleWorker(): bool {
        return $this->working_mode === self::WORKING_MODE_SWOOLE_WORKER;
    }
    public function isInSwooleTask(): bool {
        return $this->working_mode === self::WORKING_MODE_SWOOLE_TASK;
    }
    public function isInSwooleCoroutine(): bool {
        return \Swoole\Coroutine::getCid() >= 0;
    }
    public function reset() {
        if (PHP_SAPI === 'cli') {
            if (defined('SWANGO_WORKING_IN_WORKER'))
                $this->working_mode = self::WORKING_MODE_SWOOLE_WORKER;
            elseif (defined('SWANGO_WORKING_IN_TASK'))
                $this->working_mode = self::WORKING_MODE_SWOOLE_TASK;
            else
                $this->working_mode = self::WORKING_MODE_CLI;
        } elseif (PHP_SAPI === 'cgi-fcgi') {
            $this->working_mode = self::WORKING_MODE_FPM;
        } elseif (PHP_SAPI === 'apache2handler') {
            $this->working_mode = self::WORKING_MODE_APACHE;
        } else {
            $this->working_mode = self::WORKING_MODE_OTHER;
        }
    }
}