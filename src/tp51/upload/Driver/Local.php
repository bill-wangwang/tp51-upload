<?php

namespace tp51\upload\Driver;

class Local {
    public static function uploadByFile($config, $fileName, $key) {
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
    }

    public static function uploadByContent($config, $content, $key) {
        $path_key = rtrim($config['upload_path'], '/') . '/' .  $key ;
        $path = pathinfo($path_key , PATHINFO_DIRNAME);
        if(!is_dir($path)){
            if(!mkdir($path, 0755, true)){
                throw new \Exception("上传失败，没有写权限");
            }
        }
        if(!file_put_contents($path_key, $content)){
            throw new \Exception("上传失败(写入文件时出错)");
        }
        return $config['base_url'] . '/' . $key ;
    }
}