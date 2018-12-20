<?php

namespace tp51\upload\Driver;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Qiniu {
    public static function uploadImage($config, $fileName, $key) {
        $auth = new Auth($config['access_key'], $config['secret_key']);
        $token = $auth->uploadToken($config['bucket']);
        $saveName = $config['sub_dir'] . $key;
        $uploadMgr = new UploadManager();// 初始化 UploadManager 对象并进行文件的上传。
        /* $uploadMgr->putFile($token, $key, $fileName) 成功时的返回值
        array (
            0 =>
                array (
                    'hash' => 'FtLLR4t7h2zgOjCzZndpflp6Q_5t',
                    'key' => '63/68/63689e0a1bc9032a7421132cfc46d19e.jpg',
                ),
            1 => NULL,
        )
        */
        list($ret, $err) = $uploadMgr->putFile($token, $saveName, $fileName);// 调用 UploadManager 的 putFile 方法进行文件的上传。
        if ($err !== null) {
            throw new \Exception('上传到第三方失败!');
        } else {
            return $config['base_url'] . '/' . $saveName;
        }
    }
}