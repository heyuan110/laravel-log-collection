# laravel日志收集，集中管理

## 一、安装：

1. composer.json里添加包：

    `"patpat/laravel-log-collection": "dev-master",`
      
2. 执行`composer update`更新项目依赖包.

## 二、配置

1. config/app.php里添加log的Provider: 

    `\PatPat\Monolog\LogServiceProvider::class,`
    
2. 发布配置文件，执行
`php artisan vendor:publish --provider="PatPat\Monolog\LogServiceProvider"`
或
`php artisan vendor:publish`

带--provider参数只publish参数后面的provider，否则发布所有。注意一定先执行完步骤1再发布。

3. .env里添加下面配置项

  **aws配置**

    ;必须, aws区域
    LOG_COLLECTION_AWS_REGION=us-west-1
    ;必须, aws授权key值
    LOG_COLLECTION_AWS_KEY=xxxxxx
    ;必须，aws授权密钥
    LOG_COLLECTION_AWS_SECRET=xxxxxxxxxxxxxxxxxx

    注意：密钥需要能访问Kinesis Stream

   **日志配置**
    
    ;必须，日志上报的Kinesis Stream名称
    LOG_COLLECTION_KINESIS_STREAM=laravel-log
    
    ;非必须，指定日志存放目录，默认在项目storage/logs/目录下
    LOG_COLLECTION_LOG_PATH=/var/log/laravel/oms
    
    ;非必须，日志所属项目名称，方便日志写入es后区分
    LOG_COLLECTION_PROJECT_NAME=oms
    
务必先配置好上面的**必须项**
  
## 说明

调用log::info('test message')或者CLogger::getLogger('test-file-name','test-dir-anme')->info('test message')都会往本地文件写入一份.

仅设置为生产环境（.env里APP_ENV设置为staging）才会往Kinesis Stream上报数据做集中管理，本地开发测试还是在日志目录查看。

