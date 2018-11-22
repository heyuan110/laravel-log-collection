<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 8:26 PM
 */

namespace PatPat\Monolog;

use Illuminate\Log\Writer as BaseWriter;
use Monolog\Formatter\LineFormatter;

class Writer extends BaseWriter
{
    /**
     * Get a default Monolog formatter instance.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter(null, 'Y-m-d H:i:s.u', true, true);
    }
}