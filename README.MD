# laravel日志收集，集中管理

## 一、安装：

1. 在composer.json里添加：

    `"patpat/laravel-log-collection": "dev-master",`
      
2. composer update

3. 执行`php artisan vendor:publish`

## 二、配置

1. config/app.php里添加: 

    `\PatPat\Monolog\LogServiceProvider::class,`
        
2. .env里添加下面配置项

  **aws配置**

    ;必须, aws区域
    LOG_COLLECTION_AWS_REGION=us-west-1
    ;必须, aws授权key值
    LOG_COLLECTION_AWS_KEY=xxxxxx
    ;必须，aws授权密钥
    LOG_COLLECTION_AWS_SECRET=xxxxxxxxxxxxxxxxxx

    *密钥需要能访问Kinesis Stream*

   **日志配置**
    
    ;必须，日志上报的Kinesis Stream名称
    LOG_COLLECTION_KINESIS_STREAM=laravel-log
    
    ;非必须，指定日志存放目录，默认在项目storage/logs/目录下
    LOG_COLLECTION_LOG_PATH=/var/log/laravel/oms
    
    ;非必须，日志所属项目名称，方便日志写入es后区分
    LOG_COLLECTION_PROJECT_NAME=oms
    
  完成!
