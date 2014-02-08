<?php
/**
 * autoImageCrop - 图片自动缩放程序
 * 
 * @link https://github.com/mingfunwong/autoImageCrop
 * @license http://opensource.org/licenses/MIT
 * @author Mingfun Wong <mingfun.wong.chn@gmail.com>
 */
 
// 本程序提供断宽高逻辑，防止遍历生成大量文件
// 可判断变量： $width $height $mode $versions

// 例子：
if ($width > 10000 OR $height > 10000) $autoImageCrop->show_not_found();
