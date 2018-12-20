<?php

namespace tp51\upload;

use tp51\upload\Driver\Cos;
use tp51\upload\Driver\Oss;
use tp51\upload\Driver\Qiniu;
use tp51\upload\Driver\Local;

class Upload {

    private $_config = [
        'timeout'        => 180, //超时时间，单位秒
        'exception_code' => 8001, //异常错误代码
        'upload_type'    => 'local', //上传类型 ['oss', 'cos', 'qiniu', 'local']
        'max_size'       => 0, //文件最大大小，留空不限制，受php.ini和nginx配置限制，单位字节 如1M=1 * 1024 *1024
        'image_max_size' => 0, //图片文件最大大小，留空不限制，受php.ini和nginx配置限制，单位字节 如1M=1 * 1024 *1024
        'format'         => '', //文件格式，留空为不限制,|分割 （如 zip|rar|doc|jpg|png|jpeg）
        'image_format'   => '', //图片文件格式，留空为所有图片格式,|分割 （如 jpg|png|jpeg）
        'min_width'      => 0, //图片有效，最小宽度，0为不限制
        'max_width'      => 0, //图片有效，最大宽度，0为不限制
        'min_height'     => 0, //图片有效，最小高度，0为不限制
        'max_height'     => 0, //图片有效，最大高度，0为不限制
    ];

    public function __construct($config = []) {
        $this->_config = array_merge($this->_config, config('upload.'), $config);
        set_time_limit($this->_config['timeout']);
    }

    private function _getUploadType() {
        $allow_upload_type = ['oss', 'cos', 'qiniu', 'local'];
        $upload_type = strtolower($this->_config['upload_type']);
        if (in_array($upload_type, $allow_upload_type)) {
            return $upload_type;
        } else {
            throw new \Exception("暂不支持{$upload_type}的上传驱动类型", $this->_config['exception_code']);
        }
    }

    private function _checkFormat($format, $key = 'image_format') {
        $allow_format = $this->_config[$key];
        if (!$allow_format) {
            return true;
        }
        $allow_format_array = explode('|', $allow_format);
        if (is_array($allow_format_array) && in_array(strtolower($format), $allow_format_array)) {
            return true;
        }
        throw new \Exception("不允许的文件格式:{$format}，允许的格式为" . $allow_format, $this->_config['exception_code']);
    }

    private function _checkSize($size, $key = 'image_max_size') {
        $max_size = $this->_config[$key];
        if ($max_size <= 0) {
            return true;
        }
        if ($size > $max_size) {
            throw new \Exception("超过文件允许的大小限制:" . $max_size, $this->_config['exception_code']);
        }
        return true;
    }

    private function _checkImageWidthHeight($imageSize) {
        if ($this->_config['min_width'] > 0 && $imageSize[0] < $this->_config['min_width']) {
            throw new \Exception("图片最小宽度需要" . $this->_config['min_width'] . 'px', $this->_config['exception_code']);
        }
        if ($this->_config['min_height'] > 0 && $imageSize[1] < $this->_config['min_height']) {
            throw new \Exception("图片最小高度需要" . $this->_config['min_height'] . 'px', $this->_config['exception_code']);
        }
        if ($this->_config['max_width'] > 0 && $imageSize[0] > $this->_config['max_width']) {
            throw new \Exception("图片最大宽度不能超过" . $this->_config['max_width'] . 'px', $this->_config['exception_code']);
        }
        if ($this->_config['max_height'] > 0 && $imageSize[1] > $this->_config['max_height']) {
            throw new \Exception("图片最大高度不能超过" . $this->_config['max_height'] . 'px', $this->_config['exception_code']);
        }
    }


    public function uploadRemoteImage() {
        if (isset($_FILES) && is_array($_FILES) && !empty($_FILES)) {
            $fileId = '';
            foreach ($_FILES as $key => $value) {
                $fileId = $key;
                continue;
            }
            $file = request()->file($fileId);
            $info = $file->getInfo();
            //获取临时文件名
            $fileName = $info['tmp_name'];
            //获取文件后缀并转为小写，后缀不含.  hello.JPG 返回 jpg
            $fileExt = pathinfo($info['name'], PATHINFO_EXTENSION);
            //检查文件格式
            $this->_checkFormat($fileExt);
            //检查文件大小
            $fileSize = filesize($fileName);
            $this->_checkSize($fileSize);
            //获取图片宽高
            $imageSize = getimagesize($fileName);
            if (!$imageSize) {
                throw new \Exception("非图片文件", $this->_config['exception_code']);
            }
            //检查图片宽高
            $this->_checkImageWidthHeight($imageSize);
            //获取文件内容
            $content = file_get_contents($fileName);
            //获取文件key
            $key = $this->_getSaveName($content, $fileExt);

            try {
                switch ($this->_config['upload_type']) {
                    case 'oss':
                        $url = Oss::uploadImage($this->_config['oss'], $fileName, $key);
                        break;
                    case 'cos':
                        $url = Cos::uploadImage($this->_config['cos'], $fileName, $key);
                        break;
                    case 'qiniu':
                         $url = Qiniu::uploadImage($this->_config['qiniu'], $fileName, $key);
                        break;
                    case 'local':
                        $url = Local::uploadImage($this->_config['local'], $fileName, $key);
                        break;
                    default:
                        throw new \Exception("不支持的文件类型" . $this->_config['upload_type'], $this->_config['exception_code']);
                }
                return [
                    'size'   => $fileSize,
                    'url'    => $url,
                    'width'  => $imageSize[0],
                    'height' => $imageSize[1]
                ];
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), $this->_config['exception_code']);
            }
        } else {
            throw new \Exception("没有上传文件信息", $this->_config['exception_code']);
        }
    }

    /**
     * 根据文件内容和后缀生成文件名（不含上传目录）
     * @param $content
     * @param $ext
     * @return string
     */
    private function _getSaveName($content, $ext) {
        $hash = md5($content);
        return substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.' . $ext;
    }

    public function setUploadType($value) {
        $this->_config['upload_type'] = $value;
        //设置完顺便检查下类型是否允许
        $this->_getUploadType();
    }

    public function setMaxSize($value) {
        $this->_config['max_size'] = $value;
    }

    public function setFormat($value) {
        $this->_config['format'] = $value;
    }

    public function setMinWidth($value) {
        $this->_config['min_width'] = $value;
    }

    public function setMaxWidth($value) {
        $this->_config['max_width'] = $value;
    }

    public function setMinHeight($value) {
        $this->_config['min_height'] = $value;
    }

    public function setMaxHeight($value) {
        $this->_config['max_height'] = $value;
    }


}