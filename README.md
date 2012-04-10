
一个基于 [七牛云存储](http://www.qiniutek.com) [PHP 5.3 SDK](https://github.com/qiniu/php5.3-sdk) 开发的示例相册程序。

## 运行环境

- PHP5 或以上版本
- PHP 库依赖 curl , PDO, PDO_MySQL
- MySQL5 或以上版本

## 安装和运行程序

1. 用MySQL source命令（或phpMyAdmin）依次导入 sql/ 目录下的数据库和表结构源文件
2. Web服务器(比如Nginx或Apache)将应用程序的根目录指向 public/
3. 确定MySQL和Web Server正常运行，完成以上两步，即可在浏览器中体验

## 说明

1. WEB 批量上传组件用的开源 [SWFUpload v2.2.0.1](http://code.google.com/p/swfupload/)。

2. 相关钩子调用参考 public/js/handlers.js 文件中的 uploadStart(), uploadSuccess(), uploadComplete() 方法。

3. public/rs_xhr.php 和 public/op_xhr.php 两个文件在本示例中暂未用到，这两个文件封装的是php sdk接口的ajax请求和响应输出。由于当前这个demo的交互比较简单，所以没有用到。

4. 示例程序的七牛云存储认证帐号请在 lib/config.php 自行更改，这个文件可以修改程序其他设置比如数据库配置等。

5. PHP SDK 配置文件在 lib/3rd/qiniu/qbox/config.php 里边，可以修改七牛云存储远程服务主机等。

## 资源

- [七牛云存储官方网站](http://www.qiniutek.com)
- [七牛云存储开发者中心](https://dev.qiniutek.com)
- [七牛云存储API和SDK文档](http://docs.qiniutek.com)
- [七牛云存储SDK源代码](https://github.com/qiniu)
- [用PHP编写的网站，如何让网站用户在浏览器网页中直接向七牛云存储上传文件？](http://docs.qiniutek.com/v1/sdk/php5-3/#web-upload-files-directly)
