<?php
/**
 * autoImageCrop 自动缩放图片
 * 
 * 使用说明：
 * http://localhost/images/pic.png?50x100   缩放成宽50像素，高100像素的缩略图，默认缩放模式 3
 * http://localhost/images/pic.png?50x100m2   裁剪成宽50像素，高100像素的缩略图，指定缩放模式 2
 * http://localhost/images/pic.png?50x100m2&20140110   裁剪成宽50像素，高100像素的缩略图，后面字符串可用于更新版本，清除浏览器缓存
 * http://localhost/images/pic.png?50x100&20140110   缩放成宽50像素，高100像素的缩略图，默认缩放模式 3，后面字符串可用于更新版本，清除浏览器缓存
 * 
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */

/* 设置头信息 */
set_header();
/* 当前目录 */
define('ROOT', dirname(__FILE__));
/* 设置缩放图片目录 */
define('IMAGECROPDIR', ROOT . '/../thumb/%1$sx%2$s_mode%3$s/%5$s/%6$s'); // %1$s 宽, %2$s 高, %3$s 模式, %4$s 版本, %5$s 目录, %6$s 文件
/* 默认缩放模式
 * mode 1 : 强制裁剪，生成图片严格按照需要，不足放大，超过裁剪，图片始终铺满
 * mode 2 : 和1类似，但不足的时候 不放大 会产生补白，可以用png消除。
 * mode 3 : 只缩放，不裁剪，保留全部图片信息，会产生补白，
 * mode 4 : 只缩放，不裁剪，保留全部图片信息，此时的参数只是限制了生成的图片的最大宽高，不产生补白
 * mode 5 : 生成的图比例严格按照需要的比例，宽和高不超过给定的参数。
 */
define('DEFAULT_MODE', 3);
/* 默认版本 */
define('DEFAULT_VERSIONS', 1);
/* 获取宽高、缩放模式和版本 */
list($width, $height, $mode, $versions) = width_height_mode_versions();
// 此处可加入判断宽高逻辑，防止恶意遍历生成大量文件

/* 获取文件路径 */
$path = path();
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
        mk_dir(dirname($new));
    }
    /* 不存指定规格文件 */
    if (!file_exists($new))
    {
        /* 生成并输出图片 */
        require ROOT . '/ImageCrop.php';
        make_crop_thumb($old, $new, $width, $height, $mode);
    }
    file_exists($new) && show_pic($new);
    exit();
}
/* 其它处理 */
show_404();

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
    exit();
}
/**
 * 设置头信息
 */
function set_header()
{
    header('Expires: ' . date('D, j M Y H:i:s', strtotime('now + 10 years')) .' GMT');
    $etag = md5(serialize(from($_SERVER, 'QUERY_STRING')));
    if (from($_SERVER, 'HTTP_IF_NONE_MATCH') === $etag)
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
    return str_replace(str_replace('autoImageCrop/index.php', '', from($_SERVER, 'SCRIPT_NAME')), '', str_replace('?' . from($_SERVER, 'QUERY_STRING'), '', from($_SERVER, 'REQUEST_URI'))); 
}

/**
 * 获取宽高、缩放模式和版本
 */
function width_height_mode_versions()
{
    if ($query_string = from($_SERVER, 'QUERY_STRING'))
    {
        if (preg_match('/^([0-9]+)x([0-9]+)(?:m([1-5]))?(?:&([0-9]*))?$/', $query_string, $match))
        {
            if (from($match, 1) && from($match, 2))
            {
                return array($match[1], $match[2], from($match, 3, DEFAULT_MODE, TRUE), from($match, 4, DEFAULT_VERSIONS, TRUE));
            }
        }
    }
    return show_404();
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
 * 404输出
 */
function show_404()
{
    header(from($_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
    exit;
}

/**
 * 递归创建目录
 */
function mk_dir($dir, $mode = 0755) 
{ 
    if (is_dir($dir) || @mkdir($dir,$mode)) return true; 
    if (!mk_dir(dirname($dir),$mode)) return false; 
    return @mkdir($dir,$mode); 
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
