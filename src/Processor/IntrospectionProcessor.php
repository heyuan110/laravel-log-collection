<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 8:27 PM
 */

namespace PatPat\Monolog\Processor;
use Monolog\Logger;

/**
 * Monolog的IntrospectionProcessor类重写
 *
 * Class IntrospectionProcessor
 * @package App\Extensions\Monolog\Processor
 */
class IntrospectionProcessor
{
    private $level;

    private $skipClassesPartials;

    /**
     * 注意$skipClassesPartials参数，忽略Monolog和Illuminate开头的类路径
     *
     * IntrospectionProcessor constructor.
     * @param int $level
     * @param array $skipClassesPartials
     */
    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = ['Monolog\\', 'Illuminate\\'])
    {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = $skipClassesPartials;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // return if the level is not high enough
        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = debug_backtrace();

        // skip first since it's always the current method
        array_shift($trace);
        // the call_user_func call is also skipped
        array_shift($trace);

        $i = 0;

        while (isset($trace[$i]['class'])) {
            foreach ($this->skipClassesPartials as $part) {
                if (strpos($trace[$i]['class'], $part) !== false) {
                    $i++;
                    continue 2;
                }
            }
            break;
        }
        $line = null;
        if (isset($trace[$i]['class'])) {
            $line = $trace[$i]['class'];
            if (isset($trace[$i]['class'])) {
                $line = $line . '->' . $trace[$i]['function'];
            }
        } else if (isset($trace[$i - 1]['file'])) {
            $line = $trace[$i - 1]['file'];
        }
        if (null !== $line && isset($trace[$i - 1]['line'])) {
            $line = $line . ':' . $trace[$i - 1]['line'];
        }
        $record['extra']['file'] = $line;
        $record['extra']['line'] = $trace[$i - 1]['line'];
        return $record;
    }
}