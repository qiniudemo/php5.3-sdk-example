
一个基于 [七牛云存储](http://www.qiniutek.com) [PHP 5.3 SDK](https://github.com/qiniu/php5.3-sdk) 开发的示例相册程序。

## 运行环境

- PHP5 或以上版本
- PHP 库依赖 curl , PDO, PDO_MySQL
- MySQL5 或以上版本

## 安装和运行程序

1. 获取源代码。  
2. 编辑 `lib/config.php` 文件，修改其中字段 `access_key` 、 `secret_key` 、 `bucket` 和 `domain` 的值。  
3. 用MySQL source命令（或phpMyAdmin）依次导入 sql/ 目录下的数据库和表结构源文件
4. Web服务器(比如Nginx或Apache)将应用程序的根目录指向 public/  
5. 确定MySQL和Web Server正常运行，完成以上两步，即可在浏览器中体验  

## 说明

1. WEB 批量上传组件用的开源 [SWFUpload v2.2.0.1](http://code.google.com/p/swfupload/)。

2. 相关钩子调用参考 `public/assets/js/handlers.js` 文件中的 `uploadStart()`, `uploadSuccess()`, `uploadComplete()` 方法。

3. 示例程序的七牛云存储认证帐号请在 `lib/config.php` 自行更改，这个文件可以修改程序其他设置比如数据库配置等。


## 资源

- [PHP SDK 使用指南](http://docs.qiniu.com/php-sdk/index.html)  
- [用PHP编写的网站，如何让网站用户在浏览器网页中直接向七牛云存储上传文件？](http://docs.qiniutek.com/v2/sdk/php5-3/#web-upload-files-directly)
