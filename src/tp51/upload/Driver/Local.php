<?php

namespace tp51\upload\Driver;

class Local {
    public static function uploadImage($config, $fileName, $key) {

        //upload_path
        // F:/www/abc/publci/uploads/aa/bb/cc/abc.jpg
        $path_key = rtrim($config['upload_path'], '/') . '/' .  $key ;
        $path = pathinfo($path_key , PATHINFO_DIRNAME);
        if(!is_dir($path)){
            if(!mkdir($path, 0755, true)){
                throw new \Exception("上传失败，没有写权限");
            }
        }
        if(!move_uploaded_file($fileName, $path_key)){
            throw new \Exception("上传失败(移动文件时出错)");
        }
        return $config['base_url'] . '/' . $key ;

        /** @var $info */
        $info = $file->rule('md5')->move($config['upload_path']);
        if ($info) {
            $imageSize = getimagesize($config['upload_path'] . DIRECTORY_SEPARATOR . $info->getSaveName());
            $url = $config['base_url'] . '/' . str_replace('\\', '/', $info->getSaveName());
            return [
                'size'   => $info->getSize(),
                'width'  => $imageSize[0],
                'height' => $imageSize[1],
                'url'    => $url
            ];
        } else {
            Log::error("上传失败了(local)：" . $file->getError());
            throw new UploadException( "上传失败了：" . $file->getError());
        }
    }
}