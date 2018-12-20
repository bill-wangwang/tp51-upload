<?php
return [
    'upload_type' => env('app.upload_type'),
    'cos'         => [
        //按实际填写region，不同地区请参考下表，如香港请填写 ap-hongkong
        'region'                => env('upload:cos.region'),
        //按实际填写bucket，demo-125696xxxx 只需填写dmeo
        'bucket'                => env('upload:cos.bucket'),
        //指定存储的子目录，可以为空，不为空时必须以 / 结尾
        'sub_dir'               => env('upload:cos.sub_dir'),
        //按实际填写 app id
        'app_id'                => env('upload:cos.app_id'),
        //按实际填写 secret id
        'secret_id'             => env('upload:cos.secret_id'),
        //按实际填写 secret key
        'secret_key'            => env('upload:cos.secret_key'),
        'serverSide_encryption' => 'AES256',
        //按实际填写，一般用你的子域名cdn.xx.com cname到cos指定的域名
        'base_url'              => env('upload:cos.base_url'),
    ],
    'oss'         => [
        //按实际填写 access id
        'access_id'  => env('upload:oss.access_id'),
        //按实际填写 access key
        'access_key' => env('upload:oss.access_key'),
        //按实际填写endpoint，如香港则填写oss-cn-hongkong.aliyuncs.com
        'endpoint'   => env('upload:oss.endpoint'),
        //按实际填写bucket
        'bucket'     => env('upload:oss.bucket'),
        //指定存储的子目录，可以为空，不为空时必须以 / 结尾
        'sub_dir'    => env('upload:oss.sub_dir'),
        //按实际填写，一般用你的子域名cdn.xx.com cname到oss指定的域名
        'base_url'   => env('upload:oss.base_url')
    ],
    'qiniu'       => [
        //按实际填写 access key
        'access_key' => env('upload:qiniu.access_key'),
        //按实际填写 secret key
        'secret_key' => env('upload:qiniu.secret_key'),
        //按实际填写 bucket
        'bucket'     => env('upload:qiniu.bucket'),
        //指定存储的子目录，可以为空，不为空时必须以 / 结尾
        'sub_dir'    => env('upload:qiniu.sub_dir'),
        //按实际填写，一般用你的子域名cdn.xx.com cname到七牛指定的域名
        'base_url'   => env('upload:qiniu.base_url')
    ],
    'local'       => [
        //确保该目录可写
        'upload_path' => env('upload:local.upload_path', env('think_path') . '../public/uploads'),
        //请和upload以及sub_dir对应的路径保持一致
        'base_url'    => env('upload:local.base_url', '/uploads'),
    ]
];

/*
 * cos config
 * composer require qcloud/cos-sdk-v5
 * composer remove qcloud/cos-sdk-v5
 * 腾讯云cos对象存储
地域	            地域简称 region	     默认域名（上传/下载/管理 ）
=====================================================================================
北京一区（华北）	ap-beijing-1	     <bucketname-APPID>.cos.ap-beijing-1.myqcloud.com
北京	            ap-beijing	         <bucketname-APPID>.cos.ap-beijing.myqcloud.com
上海（华东）	    ap-shanghai	         <bucketname-APPID>.cos.ap-shanghai.myqcloud.com
广州（华南）	    ap-guangzhou	     <bucketname-APPID>.cos.ap-guangzhou.myqcloud.com
成都（西南）	    ap-chengdu	         <bucketname-APPID>.cos.ap-chengdu.myqcloud.com
重庆	            ap-chongqing	     <bucketname-APPID>.cos.ap-chongqing.myqcloud.com
新加坡	        ap-singapore	     <bucketname-APPID>.cos.ap-singapore.myqcloud.com
香港	            ap-hongkong	         <bucketname-APPID>.cos.ap-hongkong.myqcloud.com
多伦多	        na-toronto	         <bucketname-APPID>.cos.na-toronto.myqcloud.com
法兰克福	        eu-frankfurt	     <bucketname-APPID>.cos.eu-frankfurt.myqcloud.com
孟买	            ap-mumbai	         <bucketname-APPID>.cos.ap-mumbai.myqcloud.com
首尔	            ap-seoul	         <bucketname-APPID>.cos.ap-seoul.myqcloud.com
硅谷	            na-siliconvalley	 <bucketname-APPID>.cos.na-siliconvalley.myqcloud.com
弗吉尼亚	        na-ashburn	         <bucketname-APPID>.cos.na-ashburn.myqcloud.com
 */

/*
 * oss config
 * composer require aliyuncs/oss-sdk-php
 * composer remove aliyuncs/oss-sdk-php
 * 阿里云oss配置及相关说明
name              endpoint                          regionLocation
========================================================================
华北 1             oss-cn-qingdao.aliyuncs.com       oss-cn-qingdao
华北 2             oss-cn-beijing.aliyuncs.com       oss-cn-beijing
华北 3             oss-cn-zhangjiakou.aliyuncs.com   oss-cn-zhangjiakou
华北 5             oss-cn-huhehaote.aliyuncs.com     oss-cn-huhehaote
华东 1             oss-cn-hangzhou.aliyuncs.com      oss-cn-hangzhou
华东 2             oss-cn-shanghai.aliyuncs.com      oss-cn-shanghai
华南 1             oss-cn-shenzhen.aliyuncs.com      oss-cn-shenzhen
香港               oss-cn-hongkong.aliyuncs.com      oss-cn-hongkong
亚太东北 1 (东京)    oss-ap-northeast-1.aliyuncs.com   oss-ap-northeast-1
亚太东南 1 (新加坡)  oss-ap-southeast-1.aliyuncs.com   oss-ap-southeast-1
亚太东南 2 (悉尼)    oss-ap-southeast-2.aliyuncs.com   oss-ap-southeast-2
美国东部 1 (弗吉尼亚) oss-us-east-1.aliyuncs.com        oss-us-east-1
美国西部 1 (硅谷)    oss-us-west-1.aliyuncs.com        oss-us-west-1
中东东部 1 (迪拜)    oss-me-east-1.aliyuncs.com        oss-me-east-1
欧洲中部 1 (法兰克福) oss-eu-central-1.aliyuncs.com     oss-eu-central-1
亚太东南3 (吉隆坡)   oss-ap-southeast-3.aliyuncs.com   oss-ap-southeast-3
亚太南部 1 (孟买)    oss-ap-south-1.aliyuncs.com       oss-ap-south-1
亚太东南 5 (雅加达)  oss-ap-southeast-5.aliyuncs.com   oss-ap-southeast-5
 */

/*
 * qiniu config
 * composer require qiniu/php-sdk
 * composer remove qiniu/php-sdk
 * 七牛云存储配置及相关说明
 */
