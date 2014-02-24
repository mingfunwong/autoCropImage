<?php
/**
 * autoCropImage - 图片自动缩放程序
 * 
 * @link https://github.com/mingfunwong/autoCropImage
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */

/* 当前目录 */
define('ROOT_DIR', dirname(__FILE__));

/* 项目配置 */
require ROOT_DIR . '/_config.php';

/* 初始化 */
$autoCropImage = new autoCropImage();

/* 设置头信息 */
$autoCropImage->set_header();

/* 获取宽高、缩放模式和版本 */
list($width, $height, $mode, $versions) = $autoCropImage->width_height_mode_versions();

/* 判断生成逻辑 */
require ROOT_DIR . '/_auth.php';

/* 获取文件路径 */
$path = $autoCropImage->path();

/* 源文件 */
$old = ROOT_DIR . '/../' . IMAGES_DIR . $path;

/* 指定规格文件 */
$new = sprintf(THUMB_DIR, $width, $height, $mode, $versions, dirname($path), basename($path));

/* 存在源文件 */
if (file_exists($old))
{
    /* 不存指定规格文件夹 */
    if (!file_exists(dirname($new)))
    {
        $autoCropImage->mk_dir(dirname($new));
    }
    /* 不存指定规格文件 */
    if (!file_exists($new))
    {
        /* 生成并输出图片 */
        require ROOT_DIR . '/ImageCrop.php';
        $autoCropImage->make_crop_thumb($old, $new, $width, $height, $mode);
        exit();
    }
    file_exists($new) && $autoCropImage->show_pic($new);
    exit();
}
/* 其它处理 */
$autoCropImage->show_not_found();



/**
 * autoCropImage - 图片自动缩放程序
 * 
 * @link https://github.com/mingfunwong/autoCropImage
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */
class autoCropImage
{
    /**
     * 生成并输出图片
     * 
     * @access public
     * @param mixed $src
     * @param mixed $dst
     * @param mixed $width
     * @param mixed $height
     * @param mixed $mode
     * @return void
     */
    public function make_crop_thumb($src, $dst, $width, $height, $mode)
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
     * 
     * @access public
     * @return void
     */
    public function set_header()
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
     * 
     * @access public
     * @return string
     */
    public function path()
    {
        $path = $this->_str_replace_once($this->_str_replace_once('autoCropImage/autoCropImage.php', '', $this->from($_SERVER, 'SCRIPT_NAME')), '', $this->_str_replace_once('?' . $this->from($_SERVER, 'QUERY_STRING'), '', $this->from($_SERVER, 'REQUEST_URI')));
        return preg_replace('/(?:_)([0-9]+)x([0-9]+)(?:m([1-5]))?(?:v([A-Za-z0-9_]*))?(?:.)?(?:gif|jpg|png|GIF|JPG|PNG)?$/', '', $path);
    }
    
    /**
     * 子字符串替换一次
     * 
     * @access public
     * @param string $needle
     * @param string $replace
     * @param string $haystack
     * @return string
     */
    public function _str_replace_once($needle, $replace, $haystack) {
        $pos = strpos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }
    
    /**
     * 获取宽高、缩放模式和版本
     * 
     * @access public
     * @return array($width, $height, $mode, $versions)
     */
    public function width_height_mode_versions()
    {
        if ($request_uri = $this->from($_SERVER, 'REQUEST_URI'))
        {
            if (preg_match('/(?:gif|jpg|png|GIF|JPG|PNG)(?:_)([0-9]+)x([0-9]+)(?:m([1-5]))?(?:v([A-Za-z0-9_]*))?(?:.)?(?:gif|jpg|png|GIF|JPG|PNG)?$/', $request_uri, $match))
            {
                if ($this->from($match, 1) && $this->from($match, 2))
                {
                    return array($match[1], $match[2], $this->from($match, 3, DEFAULT_MODE, TRUE), $this->from($match, 4, DEFAULT_VERSIONS, TRUE));
                }
            }
        }
        if ($query_string = $this->from($_SERVER, 'QUERY_STRING'))
        {
            if (preg_match('/^([0-9]+)x([0-9]+)(?:m([1-5]))?(?:v([A-Za-z0-9_]*))?$/', $query_string, $match))
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
     * 
     * @access public
     * @param mixed $file
     * @return void
     */
    public function show_pic($file)
    {
        $info = getimagesize($file);
        header("Content-Type: {$info['mime']}");
        readfile($file);
        exit();
    }

    /**
     * 404 Not Found 输出
     * 
     * @access public
     * @return void
     */
    public function show_not_found()
    {
        header($this->from($_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
        exit;
    }

    /**
     * 递归创建目录
     * 
     * @access public
     * @param mixed $dir
     * @param int $mode
     * @return bool
     */
    public function mk_dir($dir, $mode = 0755) 
    { 
        if (is_dir($dir) || @mkdir($dir, $mode)) return true; 
        if (!$this->mk_dir(dirname($dir), $mode)) return false; 
        return @mkdir($dir, $mode); 
    }
    
    /**
     * 获得数组指定键的值
     * 
     * @access public
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @param bool $check_empty
     * @return mixed
     */
    public function from($array, $key, $default = FALSE, $check_empty = FALSE)
    {
        return (isset($array[$key]) === FALSE OR ($check_empty === TRUE && empty($array[$key])) === TRUE) ? $default : $array[$key];
    }
}
