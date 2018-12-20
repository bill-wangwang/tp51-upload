<?php
namespace app\index\controller;

use tp51\upload\Upload;

class Index
{
    public function upload()
    {
        $upload = new Upload();
        $res = $upload->uploadRemoteImage();
        dump($res);
    }

    public function index() {
        return view();
    }


}
