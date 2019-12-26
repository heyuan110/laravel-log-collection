<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 8:28 PM
 */

namespace PatPat\Monolog\Processor;

/**
 * ProjectProcessor
 *
 * Class ProjectProcessor
 * @package App\Extensions\Monolog\Processor
 */
class ProjectProcessor
{
    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['ip'] = \Illuminate\Support\Facades\Request::getClientIp();
        $record['extra']['server_addr'] = getenv("SERVER_ADDR");
        $record['extra']['method'] = \Illuminate\Support\Facades\Request::getMethod();;
        $record['extra']['url'] = \Illuminate\Support\Facades\Request::fullUrl();;
        $record['extra']['host'] = \Illuminate\Support\Facades\Request::getHost();;
        $record['extra']['path'] = \Illuminate\Support\Facades\Request::path();
        $record['project_path'] = base_path();
        $record['project'] = env('LOG_COLLECTION_PROJECT_NAME', 'patpat-' . md5(__FILE__ . env('APP_DEBUG', '') . env('APP_URL', '')));
        return $record;
    }
}