# laravel日志收集，集中管理

## 一、安装：

1. 在composer.json里添加：

    `"patpat/laravel-log-collection": "dev-master",`
      
2. composer update

## 二、配置

1. config/app.php里添加: 

    `\PatPat\Monolog\LogServiceProvider::class,`
        
2. 执行
`php artisan vendor:publish --provider="PatPat\Monolog\LogServiceProvider"`
或
`php artisan vendor:publish`

前面只publish当前项目，后面是发布所有，注意一定先执行步骤1添加provider

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
    
务必先配置好上面**必须项**
  
## 说明

调用log::info或者CLogger::getLogger('test')::info都会往本地文件写入一份，如果是生产环境（APP_ENV设置为staging）才会往Kinesis Stream上传.


