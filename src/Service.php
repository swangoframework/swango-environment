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
 * @property int $http_server_port
 * @property int $tcp_server_port
 * @property int $udp_server_port
 * @property int $websocket_server_port
 * @property int $terminal_server_port
 *
 * @property string $http_server_host
 * @property string $tcp_server_host
 * @property string $udp_server_host
 * @property string $websocket_server_host
 * @property string $terminal_server_host
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
            $service_config_file = self::getDir()->getParsedDir(self::$basic_config->service_config_file);
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
            'task_max_request',
            'http_server_port',
            'tcp_server_port',
            'udp_server_port',
            'websocket_server_port',
            'terminal_server_port'
        ] as $key) {
            if (isset($service_config->{$key}) && is_numeric($service_config->{$key})) {
                $number = (int)$service_config->{$key};
            } else {
                $number = $default_service_config->{$key};
            }
            $this->data->{$key} = $number;
        }

        foreach ([
            'http_server_host',
            'tcp_server_host',
            'udp_server_host',
            'websocket_server_host',
            'terminal_server_host'
        ] as $key) {
            if (isset($service_config->{$key}) && $service_config->{$key}) {
                $value = $service_config->{$key};
            } else {
                $value = $default_service_config->{$key};
            }
            $this->data->{$key} = $value;
        }

        if (property_exists($service_config, 'localip')) {
            $localip = $service_config->localip;
        } else {
            $localip = $default_service_config->localip;
        }
        $net_card_data = $ip = null;
        if (is_string($localip) && '' !== $localip) {
            if ($localip === 'aliyun-private-ipv4') {
                $ch = curl_init();
                curl_setopt_array($ch,
                    [
                        CURLOPT_HEADER => 0,
                        CURLOPT_URL => 'http://100.100.100.200/2016-01-01/meta-data/private-ipv4',
                        CURLOPT_FRESH_CONNECT => 1,
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_FORBID_REUSE => 1,
                        CURLOPT_TIMEOUT => 1
                    ]);
                $result = curl_exec($ch);
                curl_close($ch);
                if ($result) {
                    $result = trim($result);
                    if (filter_var($result, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        $ip = $result;
                    } else {
                        trigger_error('Get ip from meta data fail: ' . $result);
                    }
                } else {
                    trigger_error('Get ip from meta data error: ' . curl_error($ch));
                }
            } elseif (filter_var($localip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = $localip;
            } else {
                $file = self::getDir()->getParsedDir($localip);
                if (file_exists($file)) {
                    $ip = trim(file_get_contents($file));
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