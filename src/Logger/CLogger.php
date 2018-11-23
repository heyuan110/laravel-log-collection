<?php
/**
 * Created by patpat.
 * User: Bruce.He
 * Date: 16/4/12
 * Time: 下午3:59
 */
namespace PatPat\Monolog\Logger;

use PatPat\Monolog\Handler\AwsFirehoseHandler;
use PatPat\Monolog\Processor\IntrospectionProcessor;
use PatPat\Monolog\Processor\ProcessIdProcessor;
use PatPat\Monolog\Processor\ProjectProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Illuminate\Support\Facades\Config;

class CLogger
{
    /**
     * 自定义日志文件
     *
     * @param $name
     * @param null $dir
     * @return Logger
     */
    public static function getLogger($name, $dir = null)
    {
        $file_name = $name . '_' . date('Ymd', time()) . '.log';
        $default_log_path = Config::get('log-collection.log_path');
        $log_path = $default_log_path .'/'. ($dir ? ($dir . '/') : '') . $file_name;
        #processors
        $processors = [
            new IntrospectionProcessor(),
            new ProjectProcessor(),
            new ProcessIdProcessor(),
        ];
        #save local log files
        $streamHandler = new StreamHandler($log_path);
        $streamHandler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s.u', true, true));
        #handlers
        $handlers = [
            new AwsFirehoseHandler(),
            $streamHandler,
        ];
        $logger_name = $name?$name:env('APP_ENV','laravel');
        if($dir != null){
            $logger_name = $dir . '_' . $logger_name;
        }
        $logger = new Logger($logger_name,$handlers,$processors);
        return $logger;
    }
}