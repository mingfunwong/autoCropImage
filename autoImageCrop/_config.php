<?php
/**
 * autoImageCrop - 图片自动缩放程序
 * 
 * @link https://github.com/mingfunwong/autoImageCrop
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */

/* 设置缩放图片目录 */
define('THUMB_DIR', ROOT_DIR . '/../thumb/%1$sx%2$s_mode%3$s/%5$s/%6$s'); // %1$s 宽, %2$s 高, %3$s 模式, %4$s 版本, %5$s 目录, %6$s 文件名

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

/* 默认图片目录
 * 例如：
 * define('IMAGES_DIR', 'images/');
 * 设置后将可以：
 * 1. URL 减少使用路径 http://localhost/images/pic.jpg_50x100.jpg > http://localhost/pic.jpg_50x100.jpg
 * 2. URL 隐藏原大小图片路径
 */
define('IMAGES_DIR', '');

/* header 缓存时长 */
define('HEADER_CACHE_TIME', '10 years');

