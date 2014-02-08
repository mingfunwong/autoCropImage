autoImageCrop - 图片自动缩放程序

将图片自动缩放成指定大小，减少图片体积，从而加快下载速度，降低下载时间和成本。

## 使用说明
- http://localhost/images/pic.jpg_50x100.jpg   缩放成宽50像素，高100像素的缩略图
- http://localhost/images/pic.jpg_50x100m2.jpg   缩放成 50x100 缩略图，指定缩放模式 2
- http://localhost/images/pic.jpg_50x100v20140808.jpg   缩放成 50x100 缩略图，v字符串可用于更新版本并清除浏览器缓存
- http://localhost/images/pic.jpg_50x100m2v20140808.jpg   缩放成 50x100 缩略图，指定缩放模式 2，v字符串可用于更新版本并清除浏览器缓存

## 缩放模式说明
- mode 1 : 强制裁剪，生成图片严格按照需要，不足放大，超过裁剪，图片始终铺满。
- mode 2 : 和1类似，但不足的时候 不放大 会产生补白，可以用png消除。
- mode 3 : 只缩放，不裁剪，保留全部图片信息，会产生补白。
- mode 4 : 只缩放，不裁剪，保留全部图片信息，此时的参数只是限制了生成的图片的最大宽高，不产生补白。
- mode 5 : 生成的图比例严格按照需要的比例，宽和高不超过给定的参数。

## 服务器环境要求

PHP 5.2+

Apache mod_rewrite

## 下载
- 直接下载： https://github.com/mingfunwong/autoImageCrop/archive/master.zip
- Git： git clone git://github.com/mingfunwong/autoImageCrop.git

## 安装和测试
1. 将 `autoImageCrop/`、 `images/`、 `.htaccess` 文件放在网站根目录
2. 使用浏览器访问 `http://localhost/images/pic.jpg`、 `http://localhost/images/pic.jpg_50x100.jpg` 当第二个地址看见缩略图即安装成功

## 配置
首次使用时建议修改默认配置，文件位于 autoImageCrop/_config.php

	/* 设置缩放图片目录 */
	define('IMAGECROPDIR', ROOT . '/../thumb/%1$sx%2$s_mode%3$s/%5$s/%6$s'); // %1$s 宽, %2$s 高, %3$s 模式, %4$s 版本, %5$s 目录, %6$s 文件名
	
	/* 默认缩放模式 */
	define('DEFAULT_MODE', 3);
	
	/* 默认版本 */
	define('DEFAULT_VERSIONS', 1);
	
	/* header 缓存时长 */
	define('HEADER_CACHE_TIME', '10 years');


为了防止受到攻击者遍历生成大量文件，建议修改认证配置，文件位于 autoImageCrop/_auth.php

	// 本程序提供断宽高逻辑，防止遍历生成大量文件
	// 可判断变量： $width $height $mode $versions
	
	// 例子：
	if ($width > 10000 OR $height > 10000) $autoImageCrop->show_not_found();

## 相关链接
autoImageCrop 开源项目 [https://github.com/mingfunwong/zoek](https://github.com/mingfunwong/zoek)
