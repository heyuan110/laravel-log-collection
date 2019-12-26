<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 8:28 PM
 */

namespace PatPat\Monolog\Processor;

/**
 * 标准ProcessIdProcessor的重写
 *
 * Class ProcessIdProcessor
 * @package App\Extensions\Monolog\Processor
 */
class ProcessIdProcessor
{
    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['pid'] = getmypid();
        return $record;
    }
}