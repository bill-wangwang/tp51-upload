<?php

namespace tp51\upload\Driver;

use Qcloud\Cos\Client;

class Cos {
    public static function uploadImage($config, $fileName, $key) {
        $saveName = $config['sub_dir'] . $key;
        $cosClient = new Client([
            'region'      => $config['region'],
            'credentials' => [
                'appId'     => $config['app_id'],
                'secretId'  => $config['secret_id'],
                'secretKey' => $config['secret_key']
            ]
        ]);
        try {
            $cosClient->putObject([
                'Bucket'               => $config['bucket'] . '-' . $config['app_id'],
                'Key'                  => $saveName,
                'Body'                 => file_get_contents($fileName),
                'ServerSideEncryption' => $config['serverSide_encryption']
            ]);
            return $url = $config['base_url'] . '/' . $saveName;
        } catch (\Exception $e) {
            throw new \Exception("上传到第三方失败。" );
        }
    }
}