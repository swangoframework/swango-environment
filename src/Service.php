<?php
namespace Swango\Environment;

/**
 *
 * @author fdrea
 *
 * @property int $reactor_num
 * @property int $worker_num
 * @property int $task_worker_num
 * @property int $db_max_conntection
 * @property int $task_max_request
 *
 * @property string $local_ip
 *
 */
class Service extends \Swango\Environment {
    private $data;
    protected function __construct() {
        $this->data = new \stdClass();
        $service_config = new \stdClass();

        $default_service_config = json_decode(file_get_contents(__DIR__ . '/../default/service.json'), false);

        if (null !== self::$basic_config && isset(self::$basic_config->service_config_file) &&
             is_string(self::$basic_config->service_config_file)) {
            $service_config_file = self::$basic_config->service_config_file;
            if (file_exists($service_config_file)) {
                $service_config = json_decode(file_get_contents($service_config_file), false);
                if (json_last_error() !== JSON_ERROR_NONE)
                    $service_config = new \stdClass();
            }
        }

        foreach ([
            'reactor_num',
            'worker_num',
            'task_worker_num',
            'db_max_conntection',
            'task_max_request'
        ] as $key) {
            if (isset($service_config->{$key}) && is_numeric($service_config->{$key})) {
                $number = $service_config->{$key};
            } else {
                $number = $default_service_config->{$key};
            }
            $this->data->{$key} = (int)$number;
        }

        if (property_exists($service_config, 'localip')) {
            $localip = $service_config->localip;
        } else {
            $localip = $default_service_config->localip;
        }
        $net_card_data = $ip = null;
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
            $ip = ($net_card_data ?? swoole_get_local_ip())['eth0'];
        $this->data->local_ip = $ip;
    }
    public function __get(string $key) {
        return property_exists($this->data, $key) ? $this->data->{$key} : null;
    }
    public function __isset(string $key) {
        return isset($this->data->{$key});
    }
}