<?php

namespace tp51\upload;

use tp51\upload\Driver\Cos;
use tp51\upload\Driver\Oss;
use tp51\upload\Driver\Qiniu;
use tp51\upload\Driver\Local;

class Upload {

    //版本号
    private $version = '1.0.1';

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

    /**
     * 获取版本号
     * @return string 版本号
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * 上传内存中的图片
     * @param $content string 内存中的图片二进制内容
     * @return array ['size'=>图片字节大小, 'url'=>图片访问地址, 'width'=>图片宽度, 'height'=>图片高度]
     * @throws \Exception
     */
    public function uploadImageByContent($content) {
        //检查文件大小
        $fileSize = strlen($content);
        $this->_checkSize($fileSize);
        //$extArray 为 getimagesizefromstring()[2] 对应的格式
        $extArray = [
            '',
            'gif',
            'jpg',
            'png',
            'swf',
            'psd',
            'bmp',
            'tiff',
            'tiff',
            'jpc',
            'jp2',
            'jpx',
            'jb2',
            'swc',
            'iff',
            'wbmp',
            'xbm'
        ];
        try {
            $imageSize = getimagesizefromstring($content);
        } catch (\Exception $e) {
            throw new \Exception("不是图片文件内容", $this->_config['exception_code']);
        }
        if (!is_array($imageSize) || !isset($imageSize[2])) {
            throw new \Exception("不是图片文件内容.", $this->_config['exception_code']);
        }
        $fileExtIndex = $imageSize[2];
        if ($fileExtIndex <= 0 || $fileExtIndex > 16) {
            throw new \Exception("暂不支持的图片格式", $this->_config['exception_code']);
        }
        //获取文件后缀并转为小写，后缀不含.  hello.JPG 返回 jpg
        $fileExt = $extArray[$fileExtIndex];
        //检查文件格式
        $this->_checkFormat($fileExt);
        //检查图片宽高
        $this->_checkImageWidthHeight($imageSize);
        //获取文件key
        $key = $this->_getSaveName($content, $fileExt);
        try {
            switch ($this->_config['upload_type']) {
                case 'oss':
                    $url = Oss::uploadByContent($this->_config['oss'], $content, $key);
                    break;
                case 'cos':
                    $url = Cos::uploadByContent($this->_config['cos'], $content, $key);
                    break;
                case 'qiniu':
                    $url = Qiniu::uploadByContent($this->_config['qiniu'], $content, $key);
                    break;
                case 'local':
                    $url = Local::uploadByContent($this->_config['local'], $content, $key);
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
    }

    /**
     * 上传内存中的文件
     * @param $content string 内存中的附件二进制
     * @param $fileName string 原始文件名（用来获取文件后缀）
     * @return array ['size'=>附件字节大小, 'url'=>附件访问地址]
     * @throws \Exception
     */
    public function uploadFileByContent($content, $fileName) {
        //检查文件大小
        $fileSize = strlen($content);
        $this->_checkSize($fileSize, 'max_size');
        //获取文件后缀并转为小写，后缀不含.  hello.JPG 返回 jpg
        $fileExt = $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);;
        //检查文件格式
        $this->_checkFormat($fileExt, 'format');
        //获取文件key
        $key = $this->_getSaveName($content, $fileExt);
        try {
            switch ($this->_config['upload_type']) {
                case 'oss':
                    $url = Oss::uploadByContent($this->_config['oss'], $content, $key);
                    break;
                case 'cos':
                    $url = Cos::uploadByContent($this->_config['cos'], $content, $key);
                    break;
                case 'qiniu':
                    $url = Qiniu::uploadByContent($this->_config['qiniu'], $content, $key);
                    break;
                case 'local':
                    $url = Local::uploadByContent($this->_config['local'], $content, $key);
                    break;
                default:
                    throw new \Exception("不支持的文件类型" . $this->_config['upload_type'], $this->_config['exception_code']);
            }
            return [
                'size' => $fileSize,
                'url'  => $url
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $this->_config['exception_code']);
        }
    }

    /**
     * 上传远程图片(Form上传图片)
     * @return array ['name'=>原始文件名, 'size'=>图片字节大小, 'url'=>图片访问地址, 'width'=>图片宽度, 'height'=>图片高度]
     * @throws \Exception
     */
    public function uploadRemoteImage() {
        if (isset($_FILES) && is_array($_FILES) && !empty($_FILES)) {
            $fileId = '';
            foreach ($_FILES as $key => $value) {
                $fileId = $key;
                continue;
            }
            $file = request()->file($fileId);
            $info = $file->getInfo();
            //原始文件名
            $name = $info['name'];
            //获取临时文件名
            $fileName = $info['tmp_name'];
            //获取文件后缀并转为小写，后缀不含.  hello.JPG 返回 jpg
            $fileExt = pathinfo($info['name'], PATHINFO_EXTENSION);
            //检查文件格式
            $this->_checkFormat($fileExt);
            //检查文件大小
            $fileSize = $info['size'];
            $this->_checkSize($fileSize);
            //获取图片宽高
            try {
                $imageSize = getimagesize($fileName);
            } catch (\Exception $e) {
                throw new \Exception("非图片文件.", $this->_config['exception_code']);
            }
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
                        $url = Oss::uploadByFile($this->_config['oss'], $fileName, $key);
                        break;
                    case 'cos':
                        $url = Cos::uploadByFile($this->_config['cos'], $fileName, $key);
                        break;
                    case 'qiniu':
                        $url = Qiniu::uploadByFile($this->_config['qiniu'], $fileName, $key);
                        break;
                    case 'local':
                        $url = Local::uploadByFile($this->_config['local'], $fileName, $key);
                        break;
                    default:
                        throw new \Exception("不支持的文件类型" . $this->_config['upload_type'], $this->_config['exception_code']);
                }
                return [
                    'name'   => $name,
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
     * 上传远程附件（Form上传附件）
     * @return array ['size'=>附件字节大小, 'url'=附件访问地址]
     * @throws \Exception
     */
    public function uploadRemoteFile() {
        if (isset($_FILES) && is_array($_FILES) && !empty($_FILES)) {
            $fileId = '';
            foreach ($_FILES as $key => $value) {
                $fileId = $key;
                continue;
            }
            $file = request()->file($fileId);
            $info = $file->getInfo();
            //原始文件名
            $name = $info['name'];
            //获取临时文件名
            $fileName = $info['tmp_name'];
            //获取文件后缀并转为小写，后缀不含.  hello.JPG 返回 jpg
            $fileExt = pathinfo($info['name'], PATHINFO_EXTENSION);
            //检查文件格式
            $this->_checkFormat($fileExt, 'format');
            //检查文件大小
            $fileSize = $info['size'];
            $this->_checkSize($fileSize, 'max_size');
            //获取文件内容
            $content = file_get_contents($fileName);
            //获取文件key
            $key = $this->_getSaveName($content, $fileExt);
            try {
                switch ($this->_config['upload_type']) {
                    case 'oss':
                        $url = Oss::uploadByFile($this->_config['oss'], $fileName, $key);
                        break;
                    case 'cos':
                        $url = Cos::uploadByFile($this->_config['cos'], $fileName, $key);
                        break;
                    case 'qiniu':
                        $url = Qiniu::uploadByFile($this->_config['qiniu'], $fileName, $key);
                        break;
                    case 'local':
                        $url = Local::uploadByFile($this->_config['local'], $fileName, $key);
                        break;
                    default:
                        throw new \Exception("不支持的文件类型" . $this->_config['upload_type'], $this->_config['exception_code']);
                }
                return [
                    'name' => $name,
                    'size' => $fileSize,
                    'url'  => $url
                ];
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(), $this->_config['exception_code']);
            }
        } else {
            throw new \Exception("没有上传文件信息", $this->_config['exception_code']);
        }
    }

    /**
     * 设置上传类型 ,可以和配置文件中的不同
     * @param $value string [oss | cos | qiniu | local]
     * @throws \Exception
     */
    public function setUploadType($value) {
        $this->_config['upload_type'] = strtolower($value);
        //设置完顺便检查下类型是否允许
        $this->_getUploadType();
    }

    /**
     * 设置允许上传的最大的图片/附件的字节数大小，作用于配置文件的 image_max_size / max_size
     * @param $value int 允许上传的最大字节数（受php.ini和nginx配置的影响）
     * @param string $key max_size | image_max_size ，默认为 image_max_size 即默认为设置图片大小
     */
    public function setMaxSize($value, $key = 'image_max_size') {
        $this->_config[$key] = $value;
    }

    /**
     * 设置允许上传图片/附件的格式，
     * @param $value string 图片/附件的格式，多个用|分割开，格式不需要带上.
     * @param string $key image_format | format ，默认为 image_format 即默认为设置图片格式
     */
    public function setFormat($value, $key = 'image_format') {
        $this->_config[$key] = $value;
    }

    /**
     * 设置图片需要最小的宽度（上传图片时有效）
     * @param $value int 图片需要最小的宽度
     */
    public function setMinWidth($value) {
        $this->_config['min_width'] = $value;
    }

    /**
     * 设置图片需要最大的宽度（上传图片时有效）
     * @param $value int 图片需要最大的宽度
     */
    public function setMaxWidth($value) {
        $this->_config['max_width'] = $value;
    }

    /**
     * 设置图片需要最小的高度（上传图片时有效）
     * @param $value int 图片需要最小的高度
     */
    public function setMinHeight($value) {
        $this->_config['min_height'] = $value;
    }

    /**
     * 设置图片需要最大的高度（上传图片时有效）
     * @param $value int 图片需要最大的高度
     */
    public function setMaxHeight($value) {
        $this->_config['max_height'] = $value;
    }

}