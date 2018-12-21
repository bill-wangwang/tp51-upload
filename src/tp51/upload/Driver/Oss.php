<?php

namespace tp51\upload\Driver;

use OSS\OssClient;

class Oss {
    public static function uploadByFile($config, $fileName, $key) {
        $saveName = $config['sub_dir'] . $key;
        $bucket = $config['bucket'];
        $ossClient = new OssClient($config['access_id'], $config['access_key'], $config['endpoint'], false);
        $res = $ossClient->uploadFile($bucket, $saveName, $fileName);
        if ($res['info']['http_code'] == 200 and $res['info']['url'] != '') {
            return $config['base_url'] . '/' . $saveName;
        } else {
            throw new \Exception("上传到第三方失败");
        }
    }

    public static function uploadByContent($config, $content, $key) {
        $saveName = $config['sub_dir'] . $key;
        $bucket = $config['bucket'];
        $ossClient = new OssClient($config['access_id'], $config['access_key'], $config['endpoint'], false);
        $res = $ossClient->putObject($bucket, $saveName, $content);
        if ($res['info']['http_code'] == 200 and $res['info']['url'] != '') {
            return $config['base_url'] . '/' . $saveName;
        } else {
            throw new \Exception("上传到第三方失败");
        }
    }
}