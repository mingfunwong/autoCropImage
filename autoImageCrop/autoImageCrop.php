<?php
/**
 * autoImageCrop - 图片自动缩放程序
 * 
 * 使用说明：
 * http://localhost/images/pic.png?50x100   缩放成宽50像素，高100像素的缩略图，默认缩放模式 3
 * http://localhost/images/pic.png?50x100&20140110   缩放成 50x100 缩略图，指定缩放模式 2，v字符串可用于更新版本并清除浏览器缓存
 * 
 * @link https://github.com/mingfunwong/autoImageCrop
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */

/* 初始化 */
$autoImageCrop = new autoImageCrop();

/* 设置头信息 */
$autoImageCrop->set_header();

/* 当前目录 */
define('ROOT', dirname(__FILE__));

/* 项目配置 */
require ROOT . '/_config.php';

/* 获取宽高、缩放模式和版本 */
list($width, $height, $mode, $versions) = $autoImageCrop->width_height_mode_versions();

/* 判断生成逻辑 */
require ROOT . '/_config.php';

/* 获取文件路径 */
$path = $autoImageCrop->path();

/* 源文件 */
$old = ROOT . '/../' . $path;

/* 指定规格文件 */
$new = sprintf(IMAGECROPDIR, $width, $height, $mode, $versions, dirname($path), basename($path));

/* 存在源文件 */
if (file_exists($old))
{
    /* 不存指定规格文件夹 */
    if (!file_exists(dirname($new)))
    {
        $autoImageCrop->mk_dir(dirname($new));
    }
    /* 不存指定规格文件 */
    if (!file_exists($new))
    {
        /* 生成并输出图片 */
        require ROOT . '/ImageCrop.php';
        $autoImageCrop->make_crop_thumb($old, $new, $width, $height, $mode);
        exit();
    }
    file_exists($new) && $autoImageCrop->show_pic($new);
    exit();
}
/* 其它处理 */
$autoImageCrop->show_not_found();



/**
 * autoImageCrop - 图片自动缩放程序
 * 
 * @link https://github.com/mingfunwong/autoImageCrop
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */
class autoImageCrop
{
    /**
     * 生成并输出图片
     */
    function make_crop_thumb($src, $dst, $width, $height, $mode)
    {
        $ic = new ImageCrop($src, $dst);
        $ic->Crop($width , $height , $mode);
        list($width, $height, $type) = getimagesize($src);
        if ($type === IMAGETYPE_PNG)
        {
            $ic->OutAlpha();
            $ic->SaveAlpha();
        } else {
            $ic->OutImage();
            $ic->SaveImage();
        }
        
        $ic->destory();
    }
    
    /**
     * 设置头信息
     */
    function set_header()
    {
        header('Expires: ' . date('D, j M Y H:i:s', strtotime('now + ' . HEADER_CACHE_TIME)) .' GMT');
        $etag = md5(serialize($this->from($_SERVER, 'QUERY_STRING')));
        if ($this->from($_SERVER, 'HTTP_IF_NONE_MATCH') === $etag)
        {
            header('Etag:' . $etag, true, 304);
            exit;
        } else {
            header('Etag:' . $etag);
        }
    }

    /**
     * 获取请求路径
     */
    function path()
    {
        $path = str_replace(str_replace('autoImageCrop/autoImageCrop.php', '', $this->from($_SERVER, 'SCRIPT_NAME')), '', str_replace('?' . $this->from($_SERVER, 'QUERY_STRING'), '', $this->from($_SERVER, 'REQUEST_URI')));
        return preg_replace('/(?:_)([0-9]+)x([0-9]+)(?:m([1-5]))?(?:v([^.]*))?(?:.)?(?:gif|jpg|png)$/', '', $path);
    }

    /**
     * 获取宽高、缩放模式和版本
     */
    function width_height_mode_versions()
    {
        if ($request_uri = $this->from($_SERVER, 'REQUEST_URI'))
        {
            if (preg_match('/(?:gif|jpg|png)(?:_)([0-9]+)x([0-9]+)(?:m([1-5]))?(?:v([^.]*))?(?:.)?(?:gif|jpg|png)$/', $request_uri, $match))
            {
                if ($this->from($match, 1) && $this->from($match, 2))
                {
                    return array($match[1], $match[2], $this->from($match, 3, DEFAULT_MODE, TRUE), $this->from($match, 4, DEFAULT_VERSIONS, TRUE));
                }
            }
        }
        if ($query_string = $this->from($_SERVER, 'QUERY_STRING'))
        {
            if (preg_match('/^([0-9]+)x([0-9]+)(?:m([1-5]))?(?:v([0-9]*))?$/', $query_string, $match))
            {
                if ($this->from($match, 1) && $this->from($match, 2))
                {
                    return array($match[1], $match[2], $this->from($match, 3, DEFAULT_MODE, TRUE), $this->from($match, 4, DEFAULT_VERSIONS, TRUE));
                }
            }
        }
        return $this->show_not_found();
    }

    /**
     * 输出图片
     */
    function show_pic($file)
    {
        $info = getimagesize($file);
        header("Content-Type: {$info['mime']}");
        readfile($file);
        exit();
    }

    /**
     * 404 Not Found 输出
     */
    function show_not_found()
    {
        header($this->from($_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
        exit;
    }

    /**
     * 递归创建目录
     */
    function mk_dir($dir, $mode = 0755) 
    { 
        if (is_dir($dir) || @mkdir($dir, $mode)) return true; 
        if (!$this->mk_dir(dirname($dir), $mode)) return false; 
        return @mkdir($dir, $mode); 
    }
    
    /**
     * 获得数组指定键的值
     * 
     * @access global
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @param bool $check_empty
     * @return mixed
     */
    function from($array, $key, $default = FALSE, $check_empty = FALSE)
    {
        return (isset($array[$key]) === FALSE OR ($check_empty === TRUE && empty($array[$key])) === TRUE) ? $default : $array[$key];
    }
}
