<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 8:28 PM
 */

namespace PatPat\Monolog;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use PatPat\Monolog\Handler\AwsFirehoseHandler;
use PatPat\Monolog\Processor\IntrospectionProcessor;
use PatPat\Monolog\Processor\ProcessIdProcessor;
use PatPat\Monolog\Processor\ProjectProcessor;
use Illuminate\Support\ServiceProvider;
use PatPat\Monolog\Writer;
use Monolog\Logger;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;

class LogServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //publish
        $this->publishes([
            __DIR__ . '/config/log-collection.php' => config_path('log-collection.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
        $this->app->singleton('log', function () {
            return $this->createLogger();
        });
    }

    /**
     * Create new logger
     */
    public function createLogger()
    {
        $processors = [
            new IntrospectionProcessor(),
            new ProjectProcessor(),
            new ProcessIdProcessor(),
        ];

        $log = new Writer(
            new Logger($this->channel(), [], $processors), $this->app['events']
        );
        $this->configureHandler($log);
        return $log;

    }

    /**
     * Get the name of the log "channel".
     *
     * @return string
     */
    protected function channel()
    {
        return $this->app->bound('env') ? $this->app->environment() : 'production';
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param \Illuminate\Log\Writer $log
     * @return void
     */
    protected function configureHandler(Writer $log)
    {
        $config = $this->app['config']['log-collection'];
        $handler = $this->{'configure' . ucfirst($this->handler()) . 'Handler'}($config, $log);
        $log->getLogger()->pushHandler($handler);
        $log->getLogger()->pushHandler($this->prepareHandler(new AwsFirehoseHandler(), $config, $log));
    }

    /**
     * 日志路径
     *
     * @return mixed
     */
    protected function logPath()
    {
        return config('log-collection.log_path');
    }

    /**
     * Configure the Monolog handlers for the application.
     * @param $config
     * @param $log
     * @return HandlerInterface
     * @throws \Exception
     */
    protected function configureSingleHandler($config, $log)
    {
        $singleHander = $this->prepareHandler(
            new StreamHandler(
                $config['log_path'] . '/' . $config['log_name'] . '.log', $this->logLevel($config),
                $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
            ), $config, $log
        );
        return $singleHander;
    }

    /**
     * Configure the Monolog handlers for the application.
     * @param $config
     * @param $log
     * @return HandlerInterface
     */
    protected function configureDailyHandler($config, $log)
    {
        $dailyHandler = $this->prepareHandler(new RotatingFileHandler(
            $config['log_path'] . '/' . $config['log_name'] . '.log',
            $this->maxFiles() ?? 7, $this->logLevel(),
            $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
        ), $config, $log);
        return $dailyHandler;
    }

    /**
     * Configure the Monolog handlers for the application.
     * @param $config
     * @param $log
     * @return HandlerInterface
     */
    protected function configureSyslogHandler($config, $log)
    {
        $sysHandler = $this->prepareHandler(new SyslogHandler(
            $config['log_name'],
            $config['facility'] ?? LOG_USER, $this->logLevel()
        ), $config, $log);
        return $sysHandler;
    }


    /**
     * Configure the Monolog handlers for the application.
     * @param $config
     * @param $log
     * @return HandlerInterface
     */
    protected function configureErrorlogHandler($config, $log)
    {
        $errorHandler = $this->prepareHandler(new ErrorLogHandler(
            $config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM, $this->logLevel()
        ), $config, $log);
        return $errorHandler;
    }

    /**
     * Get the default log handler.
     *
     * @return string
     */
    protected function handler()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('log-collection.log', 'single');
        }

        return 'single';
    }

    /**
     * Get the maximum number of log files for the application.
     *
     * @return int
     */
    protected function maxFiles()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('log-collection.log_max_files', 5);
        }

        return 0;
    }

    /**
     * Get the log level for the application.
     *
     * @return string
     */
    protected function logLevel()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('app.log_level', 'debug');
        }

        return 'debug';
    }

    /**
     * Prepare the handler for usage by Monolog.
     * @param HandlerInterface $handler
     * @param array $config
     * @param $log
     * @return HandlerInterface
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function prepareHandler(HandlerInterface $handler, array $config = [], $log)
    {
        $isHandlerFormattable = false;

        if (Logger::API === 1) {
            $isHandlerFormattable = true;
        } elseif (Logger::API === 2 && $handler instanceof FormattableHandlerInterface) {
            $isHandlerFormattable = true;
        }

        if ($isHandlerFormattable && !isset($config['formatter'])) {
            $handler->setFormatter($log->getDefaultFormatter());
        } elseif ($isHandlerFormattable && $config['formatter'] !== 'default') {
            $handler->setFormatter($this->app->make($config['formatter'], $config['formatter_with'] ?? []));
        }

        return $handler;
    }

}