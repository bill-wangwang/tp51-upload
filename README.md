# tp51-upload
thinkphp5.1 上传

## 安装
> composer require php/tp51-upload

## 使用
### Controller 接收
```
use tp51\upload\Upload;
$upload = new Upload();
$res = $upload->uploadRemoteImage();
/*
$res = [
  'size'=> 图片的大小单位为字节
  'url' => 图片的URL地址
  'width' => 图片的宽
  'height' => 图片的高
]
*/
```



## 配置
- 复制`examples/config/upload.php`到项目config目录下
- 修改`.env`文件（可参考文件`examples/.env.example`和`upload.php`）

## 支持
- 阿里云的oss存储
- 腾讯云的cos存储
- 七牛云存储
- 本地存储 

## 重要选项
- upload_type 支持[ `oss` 、 `cos` 、 `qiniu` 、 `local` ]
- sub_dir 子目录选项，如果不为空必须要以`/`结尾

## 功能
- 灵活的配置（可以参考Upload.php的配置项`$_config`）
- 允许限制图片的大小(`image_max_size`)
- 允许限制图片的格式(`image_format`)
- 允许设置图片需要的最小宽度(`min_width`)
- 允许设置图片需要的最大宽度(`max_width`)
- 允许设置图片需要的最小高度(`min_height`)
- 允许设置图片需要的最大高度(`max_height`)
- 允许设置异常错误码(`exception_code`)
- 允许设置上传最大超时时间(`timeout`)