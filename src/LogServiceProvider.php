<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 8:28 PM
 */

namespace PatPat\Monolog;

use PatPat\Monolog\Handler\AwsFirehoseHandler;
use PatPat\Monolog\Processor\IntrospectionProcessor;
use PatPat\Monolog\Processor\ProcessIdProcessor;
use PatPat\Monolog\Processor\ProjectProcessor;
use Illuminate\Support\ServiceProvider;
use PatPat\Monolog\Writer;
use Monolog\Logger;

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
            __DIR__.'/config/log-collection.php' => config_path('log-collection.php'),
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
        $this->app->singleton('log',function (){
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
            new Logger($this->channel(),[],$processors),$this->app['events']
        );

        if ($this->app->hasMonologConfigurator()) {
            call_user_func($this->app->getMonologConfigurator(), $log->getMonolog());
        } else {
            $this->configureHandler($log);
        }
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
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureHandler(Writer $log)
    {
        $this->{'configure'.ucfirst($this->handler()).'Handler'}($log);
        $log->getMonolog()->pushHandler(new AwsFirehoseHandler());
    }

    /**
     * 日志路径
     *
     * @return mixed
     */
    protected function logPath()
    {
        return  \Config::get('log-collection.log_path');
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSingleHandler(Writer $log)
    {
        $log->useFiles(
            $this->logPath().'/'.\Config::get('log-collection.log_name').'.log',
            $this->logLevel()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles(
            $this->logPath().'/'.\Config::get('log-collection.log_name').'.log', $this->maxFiles(),
            $this->logLevel()
        );
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureSyslogHandler(Writer $log)
    {
        $log->useSyslog(\Config::get('log-collection.log_name'), $this->logLevel());
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureErrorlogHandler(Writer $log)
    {
        $log->useErrorLog($this->logLevel());
    }

    /**
     * Get the default log handler.
     *
     * @return string
     */
    protected function handler()
    {
        if ($this->app->bound('config')) {
            return $this->app->make('config')->get('app.log', 'single');
        }

        return 'single';
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

}