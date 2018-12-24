# tp51-upload
thinkphp5.1 聚合上传类

## 安装
> composer require phpcode/tp51-upload

## 配置
- 复制`examples/config/upload.php`到项目config配置目录下（`config/upload.php`）
- 修改`.env`文件（可参考文件`examples/.env.example`和`upload.php`）

## 使用
### Controller 接收
```
use tp51\upload\Upload;
$upload = new Upload();
$res = $upload->uploadRemoteImage();
/*
$res = [
  'name'=> 原始图片文件名
  'size'=> 图片的大小，单位为字节
  'url' => 图片的URL地址
  'width' => 图片的宽
  'height' => 图片的高
]
*/
```

## 支持
- 阿里云的oss存储
- 腾讯云的cos存储
- 七牛云存储
- 本地存储 

## 重要选项
- `upload_type` 支持[ `oss` 、 `cos` 、 `qiniu` 、 `local` ]
- `sub_dir` 子目录选项，如果不为空必须要以`/`结尾

## 功能
- 灵活的配置（可以参考Upload.php的配置项`$_config`）
- 允许限制图片的大小(`image_max_size`)或`->setMaxSize()`
- 允许限制图片的格式(`image_format`)或`->setFormat()`
- 允许设置图片需要的最小宽度(`min_width`)或`->setMinWidth()`
- 允许设置图片需要的最大宽度(`max_width`)或`->setMaxWidth()`
- 允许设置图片需要的最小高度(`min_height`)或`->setMinHeight()`
- 允许设置图片需要的最大高度(`max_height`)或`->setMaxHeight()`
- 允许设置异常错误码(`exception_code`) 
- 允许设置上传最大超时时间(`timeout`)
- 允许设置附件的格式(`format`)或`->setFormat($format, 'format')`
- 允许设置附件的最大大小(`max_size`)或`->setMaxSize($max_size, 'max_size')`

## 开放方法

### 图片部分

#### 常用
- `uploadRemoteImage()` 上传远程表单图片，常见于web
- `uploadImageByContent()` 上传内存中的图片，常见于app和微信小程序的接口
#### 进阶
- `setUploadType()` 设置上传类型 支持[ `oss` 、 `cos` 、 `qiniu` 、 `local` ]
- `setMaxSize()` 设置允许上传的图片的最大大小，单位为`字节`
- `setFormat()` 设置允许的图片格式，多个用`|`分割
- `setMinWidth()`设置图片需要的`最小宽度`，单位为`像素` 默认为`0`不限制
- `setMaxWidth()`设置图片需要的`最大宽度`，单位为`像素` 默认为`0`不限制
- `setMinHeight()`设置图片需要的`最小高度`，单位为`像素` 默认为`0`不限制
- `setMaxHeight()`设置图片需要的`最大高度`，单位为`像素` 默认为`0`不限制

### 附件部分

#### 常用
- `uploadFileByContent()`上传远程表单附件，常见于web
- `uploadRemoteFile()`上传内存中的风渐渐，常见于app和微信小程序的接口
#### 进阶
- `setUploadType()` 设置上传类型 支持[ `oss` 、 `cos` 、 `qiniu` 、 `local` ]
- `setMaxSize($max_size, 'max_size')` 设置允许上传的附件的最大大小，单位为字节
- `setFormat($format, 'format')` 设置允许的附件格式，多个用`|`分割
