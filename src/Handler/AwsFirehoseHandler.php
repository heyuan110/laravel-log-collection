<?php
/**
 * Created by PhpStorm.
 * User: bruce.he
 * Date: 2018/11/21
 * Time: 4:01 PM
 */

namespace PatPat\Monolog\Handler;

use Aws\Kinesis\Exception\KinesisException;
use Aws\Kinesis\KinesisClient;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AwsFirehoseHandler extends AbstractProcessingHandler
{
    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        //only staging can send datas to firehose
        if (env('APP_ENV') == 'staging')
        {
            //封装上传日志数据
            //记录毫秒时间
            $record['context']['log_created_at'] = $record['datetime']->format('Y-m-d H:i:s.u');
            $ks_record = [
                "created_at"=>$record['datetime']->format('Y-m-d H:i:s'),
                "project"=>$record['project'],
                "project_path"=>$record['project_path'],
                "file"=>$record['extra']['file'],
                "line"=>$record['extra']['line'],
                "url"=>$record['extra']['url'],
                "host"=>$record['extra']['host'],
                "path"=>$record['extra']['path'],
                "method"=>$record['extra']['method'],
                "ip"=>$record['extra']['ip'],
                "server_addr"=>$record['extra']['server_addr'],
                "level"=>$record['level'],
                "level_name"=>$record['level_name'],
                "channel"=>$record['channel'],
                "message"=>$record['message'],
                "context"=>json_encode($record['context']),
            ];
            //一定要加try不然如果这里报错会死循环
            try{
                $this->putRecord($ks_record);
            }catch (KinesisException $e){
                $this->errorMessage($e->getAwsErrorCode().' '.$e->getAwsErrorMessage().' '.$e->getMessage());
            }
        }
    }

    /**
     * 配置KinesisClient
     *
     * @return KinesisClient
     */
    protected function kinesisClient()
    {
        $client = new KinesisClient(
            [
                'region' => env('LOG_COLLECTION_AWS_REGION','us-west-2'),
                'version' => 'latest',
                'scheme' => 'https',
                'credentials' => [
                    'key' => env('LOG_COLLECTION_AWS_KEY','no_key'),
                    'secret' => env('LOG_COLLECTION_AWS_SECRET','no_secret'),
                ],
            ]
        );
        return $client;
    }

    /**
     * 单条上传
     */
    protected function putRecord($record)
    {
        $data = [
            "Data" => json_encode($record),
            "PartitionKey" => "patpat-partition-key-" . rand(1, 999),
            "StreamName" => env('LOG_COLLECTION_KINESIS_STREAM','no_stream')
        ];
        $this->kinesisClient()->putRecord($data);
    }

    /**
     * 批量上传
     */
    protected function putRecords($records)
    {
        $data = [
            "Records" => $records,
            "StreamName" => env('LOG_COLLECTION_KINESIS_STREAM','no_stream')
        ];
        $this->kinesisClient()->putRecords($data);
    }

    /**
     * 日志写入本地，出现异常只能写入到本地文件了，不能死循环继续上传
     * @param $msg
     * @throws \Exception
     */
    protected function errorMessage($msg){
        $default_log_path = \Config::get('log-collection.log_path'); //与log配置里的log_path目录保持一致
        $log_path = $default_log_path .'/' . 'aws_firehose_handler_error.log';
        $streamHandler = new StreamHandler($log_path);
        //用json格式，最后用kinesis agent监控aws_firehose_handler_error.log文件，方便做单行收集
        $streamHandler->setFormatter(new JsonFormatter());
        $logger = new Logger('aws_firehose_handler_error');
        $logger->pushHandler($streamHandler);
        $logger->error($msg);
    }
}