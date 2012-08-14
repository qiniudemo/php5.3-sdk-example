---
title: PHP5.3 SDK | 七牛云存储
---

# PHP5.3 SDK 使用指南

此 SDK 适用于 PHP5.3 及其以上版本。

SDK下载地址：[https://github.com/qiniu/php5.3-sdk/tags](https://github.com/qiniu/php5.3-sdk/tags)

SDK样例程序下载：[https://github.com/qiniu/php5.3-sdk-example](https://github.com/why404/qiniu-s3-php5.3-sdk-example)

**应用接入**

- [获取Access Key 和 Secret Key](#acc-appkey)
- [签名认证](#acc-auth)

**云存储接口**

- [新建资源表](#rs-NewService)
- [上传文件](#rs-PutFile)
    - [服务端上传流程](#upload-server-side)
    - [客户端上传流程](#upload-client-side)
        - [获取上传授权](#rs-PutAuth)
- [获取已上传文件信息](#rs-Stat)
- [下载文件](#rs-Get)
- [下载文件（断点续传）](#rs-GetIfNotModified)
- [下载文件（批量操作）](#rs-BatchGet)
- [发布公开资源](#rs-Publish)
- [取消资源发布](#rs-Unpublish)
- [删除已上传的文件](#rs-Delete)
- [删除所有文件（单个“表”）](#rs-Drop)

**图像处理接口**

- [获取图片属性信息](#fo-imageInfo)
- [获取指定规格的缩略图地址](#fo-imagePreview)
- [高级图像处理（缩略、裁剪、旋转、转化）](#ImageMogrifyPreviewURL)
- [高级图像处理（缩略、裁剪、旋转、转化）并持久化](#ImageMogrifyAs)
- [高级图像处理（水印）](#watermark)

**SDK使用案例**

- [用PHP编写的网站，如何让网站用户在浏览器网页中直接向七牛云存储上传文件？](#web-upload-files-directly)


## 应用接入

<a name="acc-appkey"></a>

### 1. 获取Access Key 和 Secret Key

要接入七牛云存储，您需要拥有一对有效的 Access Key 和 Secret Key 用来进行签名认证。可以通过如下步骤获得：

1. [开通七牛开发者帐号](https://dev.qiniutek.com/signup)
2. [登录七牛开发者自助平台，查看 Access Key 和 Secret Key](https://dev.qiniutek.com/account/keys) 。

### 2. 签名认证

首先，到 [https://github.com/qiniu/php5.3-sdk/tags](https://github.com/qiniu/php5.3-sdk/tags) 下载SDK源码。

然后，将SDK压缩包解压放到您的项目中，确保SDK目录中存在一个名为 config.php 的文件，编辑该文件配置您应用程序的密钥信息（Access Key 和 Secret Key）。

$ vim path/to/your_project/lib/qboxsdk/config.php

找到如下两行代码并做相应修改：

    const ACCESS_KEY = '<Please apply your access key>';
    const SECRET_KEY = '<Dont send your secret key to anyone>';

在完成 Access Key 和 Secret Key 配置后，您就可以正常使用该 SDK 提供的功能了，这些功能接下来会一一介绍。

## 云存储接口

<a name="rs-NewService"></a>

### 1. 新建资源表

新建资源表的意义在于，您可以将所有上传的资源分布式加密存储在七牛云存储服务端后还能保持相应的完整映射索引。

新建一份资源表，您只需在登录授权后实例化一个 QBox\RS\NewService() 即可，代码如下：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 首先，需要实例化一个 OAuth Client 对象
     */
    $client = QBox\OAuth2\NewClient();

    /**
     * 然后，新建资源表只需在登录后实例化一个 QBox\RS\NewService() 对象即可
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

<a name="rs-PutFile"></a>

### 2. 上传文件

<a name="upload-server-side"></a>

#### 2.1 服务端上传流程

以PHP程序作为服务端，向七牛云存储直传文件，只需调用资源表对象（`$rs`）的 `PutFile()` 方法，示例代码如下：

    require('qboxsdk/rs.php');

    /**
     * 实例化资源表对象
     */
    $client = QBox\OAuth2\NewClient();
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    /**
     * 服务端直传文件
     */
    list($result, $code, $error) = $rs->PutFile(
        $fileKey,         // 文件ID，必须
        $mimeType,        // 文件 MIME 类型，必须
        $localFile,       // 文件路径，必须
        $fileSize,        // 文件大小，单位Byte
        $timeout,         // 上传超时时间
    );
    echo "===> PutFile $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("PutFile failed: $code - $msg\n");
    }

<a name="upload-client-side"></a>

#### 2.2 客户端上传流程

然而，大多数时候，我们并不期望用PHP来上传文件。例如我们用PHP脚本来编写一个网站，倘若要给网站的用户提供文件上传的功能，自然是希望用户将文件在他的浏览器直接上传至七牛的云存储。如果我们的网站还有手机客户端应用也需要上传文件或照片，自然也是希望用户直接在他们的移动端上传至云端的存储服务器。

正如你想到的那样，应该将服务端的上传授权和客户端的直传分离开来，服务端PHP程序负责从七牛云存储获取授权的上传URL，然后将该授权的上传地址返回给客户端（可以是浏览器也可以是移动App），然后客户端程序再使用PutFile这样的思路进行端到七牛云存储的文件直接传输。

一旦理解，解决方案是水到渠成的。要实现这样的上传模型并不难，在我们的PHP网站后端我们可以用PHP SDK提供的PutAuth()方法获取授权URL，在浏览器网页上，我们可以直接使用七牛云存储接口实现直传，在手机客户端，如果是Android程序可以调用QBoxJavaSDK提供的类PutFile方法实现，要是iOS应用也可以使用QBoxObjCSDK提供的类PutFile实现上传，如果是未找到适合其他移动端开发的SDK，那还有终极解决方案，直接遵循[七牛云存储文件上传接口协议](/v2/api/io/#rs-PutFile)实现一个类似PutFile的方法即可。

<a name="rs-PutAuth"></a>

**2.2.1 获取上传授权**

所谓上传授权，就是获得一个可匿名直传的且离客户端应用程序最近的一个云存储节点的临时有效URL。

要取得上传授权，只需调用已经实例化好的资源表对象的 PutAuth() 方法。实例代码如下：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    ……

    /**
     * 新建资源表
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    ……

    /**
     * 调用资源表对象的 PutAuth() 方法
     * 取得上传授权（生成一个短期有效的可匿名上传URL）
     */
    list($result, $code, $error) = $rs->PutAuth();
    echo "===> PutAuth result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("PutFile failed: $code - $msg\n");
    }

如果请求成功，$result 会包含 url 和 expires_in 两个字段。url 字段对应的值为匿名上传的临时URL，expires_in 对应的值则是该临时URL的有效期。

一旦建立好资源表和取得上传授权，客户端程序就可以往这个URL开始上传文件了。

由于在网页向七牛云存储直接传输文件需要遵循[七牛云存储文件上传接口协议](/v2/api/io/#rs-PutFile)，而不仅仅是像调用SDK提供的PutFile()那么简单。所以，在本篇文档的最后，我们向您详细描述了该上传过程的具体实现，供您尽情查阅！

参考：[用PHP编写的网站，如何让网站用户在浏览器网页中直接向七牛云存储上传文件？](#web-upload-files-directly)


<a name="rs-Stat"></a>

### 4. 获取已上传文件信息

您可以调用资源表对象的 Stat() 方法并传入一个 Key（类似ID）来获取指定文件的相关信息。

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $fileKey = 'an_unique_key_also_can_be_a_file_name';

    /**
     * 查看文件信息
     */
    list($result, $code, $error) = $rs->Stat($fileKey);
    echo "===> Stat $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("Stat failed: $code - $msg\n");
    }

如果请求成功，得到的 $result 数组将会包含如下几个字段：

    hash: <FileETag>
    fsize: <FileSize>
    putTime: <PutTime>
    mimeType: <MimeType>

<a name="rs-Get"></a>

### 5. 下载文件

要下载一个文件，首先需要取得下载授权，所谓下载授权，就是取得一个临时合法有效的下载链接，只需调用资源表对象的 Get() 方法并传入相应的 文件ID 和下载要保存的文件名 作为参数即可。示例代码如下：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $fileKey = 'an_unique_key_also_can_be_a_file_name';
    $saveAsFriendlyName = 'example.jpg';

    /**
     * 下载授权（生成一个短期有效的可匿名下载URL）
     */
    list($result, $code, $error) = $rs->Get($fileKey, $saveAsFriendlyName);
    echo "===> Get $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("Get failed: $code - $msg\n");
    }

注意，这并不会直接将文件下载并命名为一个 example.jpg 的文件。当请求执行成功，Get() 方法返回的 $result 变量将会包含如下字段：

    url: <DownloadURL> # 获取文件内容的实际下载地址
    hash: <FileETag>
    fsize: <FileSize>
    mimeType: <MimeType>
    expires:<Seconds> ＃下载url的实际生命周期，精确到秒

如果你是在网页中显示这张名为 example.jpg 的图片，HTML代码如下：

    <img src="<?php echo $result["url"]; ?>" />

浏览器上鼠标右键保存这张图片，默认保存的名字将会是之前 $saveAsFriendlyName 变量指定的 example.jpg 。

<a name="rs-GetIfNotModified"></a>

### 6. 下载文件（断点续传）

这里所说的断点续传指断点续下载，所谓断点续下载，就是已经下载的部分不用下载，只下载基于某个“游标”之后的那部分文件内容。相对于资源表对象的 Get() 方法，调用断点续下载方法 GetIfNotModified() 需额外再传入一个 $baseVersion 的参数（如result['hash']，result为需要续传的Get()的第一个返回值）作为下载的内容起点。示例代码如下：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $fileKey = 'an_unique_key_also_can_be_a_file_name';
    $saveAsFriendlyName = 'example.jpg';

    /**
     * 下载授权（生成一个短期有效的可匿名下载URL）
     */
    list($result, $code, $error) = $rs->Get($fileKey, $saveAsFriendlyName);
    echo "===> Get $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("Get failed: $code - $msg\n");
    }

    /**
     * 下载授权（生成一个短期有效的可匿名下载URL），如果服务端文件未被修改（用于断点续传）
     */
    list($result, $code, $error) = $rs->GetIfNotModified(
        $fileKey,
        $saveAsFriendlyName,
        $result['hash']
    );
    echo "===> GetIfNotModified $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("GetIfNotModified failed: $code - $msg\n");
    }

GetIfNotModified() 方法返回的结果包含的字段同 Get() 方法一致。

<a name="rs-BatchGet"></a>

### 7. 下载文件（批量操作）

调用资源表对象的 BatchGet() 方法，可以传递多个文件ID同时获取多个短期有效的可用下载链接。

BatchGet() 方法有一个参数，参数类型为数组 Array，该参数可以有如下两种规格：

1. string 型，由 string 字符串组成的一维数组，string 表示文件ID（fileKey）；

2. array 型，由 key => value 组成的键值对数组；规格是：

   [array('key' => $key, 'attName' => $attName, 'expires' => 3600), …]

   其中 'attName'(用于下载要保存的文件名)、'expires'(显示地指定下载链接的有效期) 为可选。

以下是一个批量获取下载授权的示例：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $key1 = 'an_unique_key_also_can_be_a_file_name';
    $key2 = 'yet_another_unique_key_also_can_be_a_file_name_too';

    /**
     * 用法一：
     * 批量下载授权（生成一堆短期有效的可匿名下载URL）
     */
    list($result, $code, $error) = $rs->BatchGet(array($key1, $key2));
    echo "===> BatchGet $key result:\n";
    if ($code == 200) {
	    var_dump($result);
    } else {
	    $msg = QBox\ErrorMessage($code, $error);
	    die("BatchGet failed: $code - $msg\n");
    }

    /**
     * 用法二：
     * 批量下载授权（生成一堆短期有效的可匿名下载URL）
     */
    $friendlyName1 = 'example1.jpg';
    $friendlyName2 = 'example2.png';
    list($result, $code, $error) = $rs->BatchGet(array(
        array("key" => $key1, "attName" => $friendlyName1),
        array("key" => $key2, "attName" => $friendlyName2, "expires" => 3600)
    ));
    echo "===> BatchGet $key result:\n";
    if ($code == 298) {
        var_dump($result);
    } else {
	    $msg = QBox\ErrorMessage($code, $error);
	    die("BatchGet failed: $code - $msg\n");
    }

<a name="rs-Publish"></a>

### 8. 发布公开资源

使用七牛云存储提供的资源发布功能，您可以将一个资源表里边的所有文件以静态链接可访问的方式公开发布到您自己的域名下。

要公开发布一个资源表里边的所有文件，只需调用改资源表对象的 Publish() 方法并传入 域名 作为参数即可。如下示例：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $accessDomain = 'cdn.example.com';

    /**
     * 将 CustomTableName 表里边的内容作为静态资源发布。
     * 静态资源访问的url格式为：http://$accessDomain/fileKey
     */
    list($code, $result) = $rs->Publish($accessDomain);
    echo "===> Publish to $accessDomain result:\n";
    if ($code == 200) {
	    var_dump($result);
    } else {
	    $msg = QBox\ErrorMessage($code, $error);
	    die("Publish to $accessDomain failed: $code - $msg\n");
    }

<a name="rs-Unpublish"></a>

### 9. 取消资源发布

调用资源表对象的 Unpublish() 方法可取消该资源表内所有文件的静态外链。

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $accessDomain = 'cdn.example.com';

    /**
     * 将 CustomTableName 表里边的文件全部取消静态外链。
     */
    list($code, $result) = $rs->Unpublish($accessDomain);
    echo "===> Unpublish to $accessDomain result:\n";
    if ($code == 200) {
	    var_dump($result);
    } else {
	    $msg = QBox\ErrorMessage($code, $error);
	    die("Unpublish to $accessDomain failed: $code - $msg\n");
    }

<a name="rs-Delete"></a>

### 10. 删除已上传的文件

要删除指定的文件，只需调用资源表对象的 Delete() 方法并传入 文件ID（key）作为参数即可。如下示例代码：

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $key = 'an_unique_key_also_can_be_a_file_name';

    /**
     * 删除指定的文件
     */
	list($code, $error) = $rs->Delete($key);
	echo "===> Delete $key result:\n";
	if ($code == 200) {
		echo "Delete file $key ok!\n";
	} else {
		$msg = QBox\ErrorMessage($code, $error);
		die("Delete failed: $code - $msg\n");
	}

<a name="rs-Drop"></a>

### 11. 删除所有文件（单个“表”）

要删除整个资源表及该表里边的所有文件，可以调用资源表对象的 Drop() 方法。

需慎重，这会删除整个表及其所有文件

    require('qboxsdk/rs.php');
    require('qboxsdk/client/rs.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    /**
     * 删除整个表及其所有文件
     */
	list($code, $error) = $rs->Drop();
	echo "===> Drop table result:\n";
	if ($code == 200) {
		echo "Drop $bucket ok!\n";
	} else {
		$msg = QBox\ErrorMessage($code, $error);
		die("Drop $bucket failed: $code - $msg\n");
	}

## 图像处理接口

<a name="fo-imageInfo"></a>

### 1. 获取图片属性信息

SDK 提供的 `QBox\FileOp\ImageInfoURL()` 方法，可以让开发者基于图片的下载链接 `$imageDownloadURL` 生成获取该图片属性信息的 URL。

**规格**

    QBox\FileOp\ImageInfoURL($imageDownloadURL)

**参数**

$imageDownloadURL
: 图片下载链接，由资源表对象的 Get() 方法取得的图片下载URL

**返回**

返回一个字符串类型的缩略图 URL 。

**示例**

如下示例，首先，调用资源表对象的 Get() 方法取得图片的下载URL；接着，再将该下载URL作为参数传入 QBox\FileOp\ImageInfoURL() 方法中用来构造一个新的访问图片信息的URL，然后http请求该URL即可。

    require('qboxsdk/rs.php');
    require('qboxsdk/fileop.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $fileKey = 'an_unique_key_also_can_be_a_file_name';
    $saveAsFriendlyName = 'example.png';

    /**
     * 下载授权（生成一个短期有效的可匿名下载URL）
     */
    list($result, $code, $error) = $rs->Get($fileKey, $saveAsFriendlyName);
    echo "===> Get $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("Get failed: $code - $msg\n");
    }

    /**
     * 获取图片信息
     */
    $urlImageInfo = QBox\FileOp\ImageInfoURL($result['url']);
    $imgInfo = file_get_contents($urlImageInfo);
    echo "===> ImageInfo of $fileKey:\n";
    echo $imgInfo. "\n";

如果请求成功，$imgInfo 将会是一个字符串格式的JSON，包含如下字段：

    {
        format: <ImageType> // "png", "jpeg", "gif", "bmp", etc.
        width: <ImageWidth>
        height: <ImageHeight>
        colorModel: <ImageColorModel> // "palette16", "ycbcr", etc.
    }

您可以用 json_decode($imgInfo); 将其转化为数组结构。

<a name="fo-imagePreview"></a>

### 2. 获取指定规格的缩略图地址

SDK 提供的 `QBox\FileOp\ImagePreviewURL()` 方法，可以让开发者基于图片的下载链接 `$imageDownloadURL` 与缩略图规格的枚举值 `$thumbType` 生成特定规格的缩略图预览地址。

**规格**

    QBox\FileOp\ImagePreviewURL($imageDownloadURL, $thumbType)

**参数**

$imageDownloadURL
: 图片下载链接，由资源表对象的 Get() 方法取得的图片下载URL

$thumbType
: 缩略图规格，参考 [七牛云存储API之缩略图预览](/v2/api/foimg/#fo-imagePreview) 和 [自定义缩略图规格](/v2/api/foimg/#fo-imagePreviewEx) 。

**返回**

返回一个字符串类型的缩略图 URL 。

**示例**

如下示例，首先，调用资源表对象的 Get() 方法取得图片的下载 URL；接着，再将该下载URL作为参数传入 QBox\FileOp\ImagePreviewURL() 方法中，同时传入一个缩略图规格的枚举值，以此来构造一个特地规格缩略图的URL。代码如下：

    require('qboxsdk/rs.php');
    require('qboxsdk/fileop.php');

    /**
     * 实例化资源表对象
     */
    $bucket = 'CustomTableName';
    $rs = QBox\RS\NewService($client, $bucket);

    $fileKey = 'an_unique_key_also_can_be_a_file_name';
    $saveAsFriendlyName = 'example.png';

    /**
     * 下载授权（生成一个短期有效的可匿名下载URL）
     */
    list($result, $code, $error) = $rs->Get($fileKey, $saveAsFriendlyName);
    echo "===> Get $key result:\n";
    if ($code == 200) {
        var_dump($result);
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("Get failed: $code - $msg\n");
    }

    /**
     * 基于下载链接构造缩略图地址
     * 第二个参数值为 0 表示输出宽800px高600px图片质量为85的缩略图
     */
    $imagePreviewURL = QBox\FileOp\ImagePreviewURL($result["url"], 0);


<a name="ImageMogrifyPreviewURL"></a>

### 3. 高级图像处理（缩略、裁剪、旋转、转化）

`QBox\FileOp\ImageMogrifyPreviewURL()` 方法支持将一个存储在七牛云存储的图片进行缩略、裁剪、旋转和格式转化处理，该方法返回一个可以直接预览缩略图的URL。

    $imageMogrifyPreviewURL = QBox\FileOp\ImageMogrifyPreviewURL($src_img_url, $mogrify_options);

**参数**

$src_img_url
: 必须，字符串类型（string），指定原始图片的下载链接，可以根据 `QBox\RS\Service()` 实例化对象的 `Get()` 获取到。

$mogrify_options
: 必须，数组（Array），Hash Map 格式的图像处理参数。

`$mogrify_options` 对象具体的规格如下：

    $mogrify_options = array(
        "thumbnail" => <ImageSizeGeometry>,
        "gravity" => <GravityType>, =NorthWest, North, NorthEast, West, Center, East, SouthWest, South, SouthEast
        "crop" => <ImageSizeAndOffsetGeometry>,
        "quality" => <ImageQuality>,
        "rotate" => <RotateDegree>,
        "format" => <DestinationImageFormat>, =jpg, gif, png, tif, etc.
        "auto_orient" => <TrueOrFalse>
    );

`QBox\FileOp\ImageMogrifyPreviewURL()` 方法是对七牛云存储图像处理高级接口的完整包装，关于 `$mogrify_options` 参数里边的具体含义和使用方式，可以参考文档：[图像处理高级接口](#/v2/api/foimg/#fo-imageMogr)。

<a name="ImageMogrifyAs"></a>

### 4. 高级图像处理（缩略、裁剪、旋转、转化）并持久化存储处理结果

`QBox\RS\Service()` 实例化对象的 `ImageMogrifyAs()` 方法支持将一个存储在七牛云存储的图片进行缩略、裁剪、旋转和格式转化处理，并且将处理后的缩略图作为一个新文件持久化存储到七牛云存储服务器上，这样就可以供后续直接使用而不用每次都传入参数进行图像处理。

    $client = QBox\OAuth2\NewClient();
    $imgrs  = QBox\RS\NewService($client, "thumbnails_bucket");
    
    list($result, $code, $error) = $imgrs->ImageMogrifyAs($target_key, $src_img_url, $mogrify_options);

**参数**

$target_key
: 必须，字符串类型（string），指定处理后要保存的缩略图的唯一文件ID

$src_img_url
: 必须，字符串类型（string），指定原始图片的下载链接，可以根据 `QBox\RS\Service()` 实例化对象的 `Get()` 获取到。

$mogrify_options
: 必须，数组（Array），Hash Map 格式的图像处理参数。

`$mogrify_options` 对象具体的规格如下：

    $mogrify_options = array(
        "thumbnail" => <ImageSizeGeometry>,
        "gravity" => <GravityType>, =NorthWest, North, NorthEast, West, Center, East, SouthWest, South, SouthEast
        "crop" => <ImageSizeAndOffsetGeometry>,
        "quality" => <ImageQuality>,
        "rotate" => <RotateDegree>,
        "format" => <DestinationImageFormat>, =jpg, gif, png, tif, etc.
        "auto_orient" => <TrueOrFalse>
    );

`QBox\RS\Service()` 实例化对象的 `ImageMogrifyAs()` 方法是对七牛云存储图像处理高级接口的完整包装，关于 `$mogrify_options` 参数里边的具体含义和使用方式，可以参考文档：[图像处理高级接口](#/v2/api/foimg/#fo-imageMogr)。

**注意**

在上述示例代码中，我们实例化了一个新的 `$imgrs` 对象，之所以这么做是因为我们考虑到缩略图也许可以创建公开外链，即缩略图所存放的 `thumbnails_bucket` 可以通过调用 `$imgrs->Publish()` 方法公开从而提供静态链接直接访问，这样做的好处是限定了作用域仅限于 `thumbnails_bucket`，也使得缩略图不必通过API通道进行请求且使用静态CDN加速访问，同时也保证了原图不受任何操作影响。

为了使得调用 `$imgrs->ImageMogrifyAs()` 方法有实际意义，客户方的业务服务器必须保存 `thumbnails_bucket` 和 `$imgrs.ImageMogrifyAs` 方法中参数 `$target_key` 的值。如此，该缩略图作为一个新文件可以使用 SDK 提供的任何方法。


## SDK使用案例

<a name="web-upload-files-directly"></a>

### 1. 用PHP编写的网站，如何让网站用户在浏览器网页中直接向七牛云存储上传文件？

在前面 [上传文件——客户端上传流程](#upload-client-side) 一小结中，我们已经清晰地了解到解决思路： 在PHP网站后端我们可以用PHP SDK提供的PutAuth()方法获取授权URL，然后将该URL放置于要用于文件上传的浏览器网页上，在浏览器端我们可以遵循 [七牛云存储的上传接口协议](/v2/api/io/#rs-PutFile) 来实现文件从端到云（存储）的直接传输。

此时，如果您尚未了解七牛云存储上传文件的接口，建议先熟悉下：[七牛云存储的上传接口协议](/v2/api/io/#rs-PutFile)

如果您需要使用HTML Form来上传一个文件，以下是一个符合七牛云存储文件上传接口协议的表单模型：

    <form enctype="multipart/form-data" action="{$AuthorizedUploadURL}" method="POST">
        <input type="hidden" name="action" value="/rs-put/{EncodedEntryURI}/mimeType/{EncodedMimeType}" />
        <input type="hidden" name="params" value="filename={thisFileName}&fileKey={thisFileKey}&tbname={ResourceTableName}"
        Choose a file to upload: <input name="file" type="file" />
        <input type="submit" value="Upload File" />
    </form>

若您看过[七牛云存储的上传接口协议](/v2/api/io/#rs-PutFile)，那么以上HTML代码并不陌生，这是七牛云存储上传接口HTML版本的multipart/form-data表达形式，片段中花括号{}部分是要替换掉的变量，$AuthorizedUploadURL 变量可以由PHP输出，其他input域里边的变量不得不通过前端JavaScript动态实现，而且还要考虑跨域上传成功后的跳转（或回调）处理。HTML里边直传确实比较特殊，不像手持端应用可以直接调SDK的辅助上传方法。看似简单的表单上传，里边却有着不可简化的些许复杂性，为什么不让我们换种不需要纠结更易于抉择的思维方式呢？

实事上我们能想到更好的解决方案，在接下来的示例中，我们不会使用HTML Form直接跨域上传一个文件，虽然可以这么做，但是前后交互处理实现起来并不是那么划算。示例中我们更倾向于使用开源的SWFUpload组件来实现批量上传，除了批量上传比较方便，展示效果比较丰富灵活以外，还能读到上传的文件属性信息等（比如文件名，大小，MimeType，修改时间等。这不正是我们想要的吗，甚至不用作为回调参数传递了哦也）。当然，也许您会更倾向HTML5和AJAX的方案，自然也是可行的，只不过您需要根据实际情况衡量浏览器兼容性方面的得与失。

不管是SWFUpload还是HTML5和AJAX的解决方案，顺着此篇文档看下去您都能顺滕摸瓜了解个透彻，那样无论用什么解决方案，您都会知晓怎么处理与如何实现。

假设有如下目录结构的一个PHP网站程序，其中 public/ 为网站根目录， lib/ 目录存放配置文件和类库（比如QBoxSDK）等。

    ├── lib
    │   ├── config.php
    │   ├── pdo.class.php
    │   └── qboxsdk
    │       ├── oauth/
    │       ├── client
    │       │   ├── curl.php
    │       │   └── rs.php
    │       ├── config.php
    │       ├── fileop.php
    │       ├── oauth.php
    │       ├── rs.php
    │       └── utils.php
    ├── public
        ├── bootstrap.php
        ├── callback.php
        ├── index.php
        └── assets
            ├── css/
            ├── images/
            ├── js
            │   ├── jquery.js
            │   ├── uniqid.js
            │   ├── utf8_encode.js
            │   ├── base64_encode.js
            │   ├── helper.js
            │   ├── fileprogress.js
            │   ├── swfupload.queue.js
            │   └── handlers.js
            └── swfupload
                ├── swfupload.js
                ├── swfupload.swf
                └── swfuploadbutton.swf

首先，我们需要编辑 `lib/qboxsdk/config.php` 文件配置相应的 Access Key 和 Secret Key 。

$ vim lib/qboxsdk/config.php

    const ACCESS_KEY = '<Please apply your access key>';
    const SECRET_KEY = '<Dont send your secret key to anyone>';

接着我们在 public/boostrap.php 组织并引入程序需要的文件，代码和注解如下：

$ vim public/boostrap.php

    /**
     * 定义网站目录结构
     */
    define('ROOT_DIR', str_replace(array('\\\\', '//'), DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
    define('LIB_DIR', ROOT_DIR . 'lib' . DIRECTORY_SEPARATOR);
    define('QBOX_SDK_DIR', LIB_DIR . 'qbox' . DIRECTORY_SEPARATOR);

    /**
     * 加载配置文件
     */
    require_once LIB_DIR . 'config.php';
    require_once LIB_DIR . 'pdo.class.php';

    /**
     * 设置错误报告级别
     */
    error_reporting($config['error']['reporting']);

    /**
     * 初始化数据库连接句柄
     */
    $db = Core_Db::getInstance($config["db"]);

    /**
     * 加载QBoxSDK类库文件
     */
    require_once QBOX_SDK_DIR . 'oauth.php';
    require_once QBOX_SDK_DIR . 'rs.php';
    require_once QBOX_SDK_DIR . 'fileop.php';
    require_once QBOX_SDK_DIR . 'client/rs.php';

    /**
     * 初始化 OAuth Client Transport
     */
    $client = QBox\OAuth2\NewClient();

    /**
     * 初始化 Qbox Reource Service Transport
     */
    $tableName = $config["qbox"]["tb_name"];
    $rs = QBox\RS\NewService($client, $tableName);

接下来，我们在上传页面中引入并调用 SWFUpload 组件。

$ vim public/upload.php

    <?php
    require_once 'bootstrap.php';

    /**
     * 调用资源表对象的 PutAuth() 方法
     * 取得上传授权（生成一个短期有效的可匿名上传URL）
     */
    list($result, $code, $error) = $rs->PutAuth();
    echo "===> PutAuth result:\n";
    if ($code == 200) {
        $upload_url = $result["url"];
    } else {
        $msg = QBox\ErrorMessage($code, $error);
        die("PutFile failed: $code - $msg\n");
    }

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" >
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>QBox OpenSDK Demo - Upload Demo</title>
    <script type="text/javascript">
        var rsTableName = '<?php echo $tableName; ?>';
    </script>
    <link href="css/default.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/utf8_encode.js"></script>
    <script type="text/javascript" src="js/base64_encode.js"></script>
    <script type="text/javascript" src="js/uniqid.js"></script>
    <script type="text/javascript" src="js/helper.js"></script>
    <script type="text/javascript" src="swfupload/swfupload.js"></script>
    <script type="text/javascript" src="js/swfupload.queue.js"></script>
    <script type="text/javascript" src="js/fileprogress.js"></script>
    <script type="text/javascript" src="js/handlers.js"></script>
    <script type="text/javascript">
    var swfu;
    window.onload = function() {
      var settings = {
        flash_url : "/assets/swfupload/swfupload.swf",
        upload_url: "<?php echo $upload_url; ?>",
        post_params: {},
        use_query_string: false,
        file_post_name: "file",
        file_size_limit : "10 MB",
        file_types : "*.png;*.jpg;*.jpeg;*.gif",
        file_types_description: "Web Image Files",
        file_upload_limit : 100,
        file_queue_limit : 0,
        custom_settings : {
            fileUniqIdMapping : {},
            progressTarget : "fsUploadProgress",
            cancelButtonId : "btnCancel"
        },
        debug: false,

        // Button Settings
        button_image_url : "/assets/images/XPButtonUploadText_61x22.png",
        button_placeholder_id : "spanButtonPlaceholder1",
        button_width: 61,
        button_height: 22,

        // The event handler functions are defined in handlers.js
        file_queued_handler : fileQueued,
        file_queue_error_handler : fileQueueError,
        file_dialog_complete_handler : fileDialogComplete,
        upload_start_handler : uploadStart,
        upload_progress_handler : uploadProgress,
        upload_error_handler : uploadError,
        upload_success_handler : uploadSuccess,
        upload_complete_handler : uploadComplete,
        queue_complete_handler : queueComplete // Queue plugin event
      };

      swfu = new SWFUpload(settings);
    };
    </script>
    </head>
    <body>
    <div id="content">
        <p>请选择任意图片，支持批量多选上传。</p>
        <br />
        <form method="post" enctype="multipart/form-data">
            <div class="fieldset flash" id="fsUploadProgress">
                <span class="legend">上传列表</span>
            </div>
            <div id="divStatus">0 Files Uploaded</div>
            <div style="padding-left: 5px;">
                <span id="spanButtonPlaceholder1"></span>
                <input id="btnCancel" type="button" value="Cancel All Uploads"
                 onclick="swfu.cancelQueue();" disabled="disabled"
                 style="margin-left: 2px; height: 22px; font-size: 8pt;" />
            </div>
        </form>
    </div>
    </body>
    </html>

在如上代码JavaScript的settings变量中，我们设定 SWFUpload 上传过程中的各种钩子回调都与 public/assets/js/handlers.js 里边定义的事件函数一一对应。在上传每一个文件前都需要给SWFUpload隐形表单动态增加一个名字为action的input域（字段），用以构建七牛云存储上传文件接口的标准multipart-form所需要的元素（名为params的input域是可选的，若不传七牛云存储服务端不会向应用的业务服务器发送回调）。

要在开始上传文件前给SWFUpload隐形表单动态添加字段，只需修改 public/assets/js/handlers.js 文件中的 uploadStart() 函数：

    // 定义一个文件上传前要执行的业务逻辑
    function uploadStart(file) {
      try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("Uploading...");
        progress.toggleCancel(true, this);

        // 首先，为该文件生成一个唯一ID
        // uniqid() 函数在 public/assets/js/uniqid.js 文件中有定义
        var fileUniqKey = uniqid(file.name);

        // 然后构造 action 表单域的值，generate_rs_put_path() 在 public/assets/js/helper.js 中有定义
        var action = generate_rs_put_path(rsTableName, fileUniqKey, file.type);

        // 给隐形表单添加名为 action 的 input 域（字段）
        this.addPostParam("action", action);

        // 给隐形表单添加名为 params 的 input 域（字段）
        // params 里边的数据，用于文件上传成功后，七牛云存储服务器向我们的业务服务器执行 POST 回调
        this.addPostParam("params", "filename="+file.name+"&filekey="+fileUniqKey+"&filetype="+file.type);

        // 将该文件唯一ID临时保存起来供后续使用
        this.customSettings.fileUniqIdMapping[file.id] = fileUniqKey;
      } catch (ex) {}
      return true;
    }

根据代码注释想必你能看到我们做了什么事情，目的只有一个，给上传用的隐形表单在上传文件之前添加一个名为action的表单域。上面的 generate_rs_put_path() 函数在 public/assets/js/helper.js 中有定义，作用是生成 action 表单域的值，其值的规格为："/rs-put/\<[EncodedEntryURI](/v2/api/words/#EncodedEntryURI)\>/mimeType/\<[EncodedMimeType](/v2/api/words/#EncodedMimeType)\>"

JavaScript 文件 public/assets/js/helper.js 定义了一些辅助函数：

    // 生成用于URL安全传输的base64编码字符
    function urlsafe_base64_encode(content)
    {
        // base64_encode() 方法在 public/assets/js/base64_encode.js 中有定义
        return base64_encode(content).replace(/\+/g, '-').replace(/\//g, '_');
    }

    // 生成格式为 /rs-put/<EncodedEntryURI>/mimeType/<EncodedMimeType> 这样的字符串
    function generate_rs_put_path(tbName, fileKey, mimeType)
    {
        mimeType = mimeType || 'application/octet-stream';
        var entryURI = tbName + ':' + fileKey;
        var result = '/rs-put/' + urlsafe_base64_encode(entryURI) +
                     '/mimeType/' + urlsafe_base64_encode(mimeType);
        return result;
    }

我们没有给SWFUpload隐形表单添加名为params的表单域，原因是我们在客户端通过SWFUpload就已经知道了一个文件的属性信息比如文件名称，文件大小等，还包括我们之前在 uploadStart() 里边临时暂存的用于存储在七牛服务端的文件唯一ID和资源表名称。所以，我们等一个文件上传成功后就能在客户端向我们网站的业务服务器执行回调，而无需绕圈将这些信息传递给七牛服务器然后七牛服务器回调给我们的业务服务器。在SWFUpload中这很好办，只需在 handlers.js 中修改 uploadSuccess() 方法的业务逻辑即可。代码如下：

    // 定义一个文件上传成功后要处理的业务逻辑
    function uploadSuccess(file, serverData) {
      try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setComplete();
        progress.setStatus("Complete.");
        progress.toggleCancel(false);

        // 取出之前在 uploadStart() 暂存的文件唯一ID
        var fileUniqKey = this.customSettings.fileUniqIdMapping[file.id];

        // 组织要回调给网站业务服务器的数据
        var postData = {
          "action": "insert",
          "file_key": fileUniqKey,
          "file_name": file.name,
          "file_size": file.size,
          "file_type": file.type
        };

        // 通过AJAX异步向网站业务服务器POST数据
        $.ajax({
          type: "POST",
          url: 'callback.php',
          processData: true,
          data: postData,
          dataType: "json",
          success:function(resp){}
        });

        } catch (ex) {
          this.debug(ex);
        }
    }

在网站业务服务器要处理前端 Ajax 发送过来的请求，即将上传完成的文件信息记录到网站数据库中方便后续操作处理（比如从七牛云存储查看或删除文件）。

public/callback.php 源码如下：

    <?php
    # 定义http headers输出
    header('Pragma: no-cache');
    header('Cache-Control: no-store');
    header('Content-type: application/json');

    require_once 'bootstrap.php';

    /**
     * 响应请求
     */

    # 取得将要执行操作的类型
    $act = isset($_POST["action"]) ? strtolower(trim($_POST["action"])) : "";

    switch ($act) {

        # 如果是写表操作
        case "insert":

            # 首先接值
            $filekey = isset($_POST["file_key"]) ? trim($_POST["file_key"]) : "";
            $filename = isset($_POST["file_name"]) ? trim($_POST["file_name"]) : "";
            $filesize = isset($_POST["file_size"]) ? (int)trim($_POST["file_size"]) : 0;
            $filetype = isset($_POST["file_type"]) ? trim($_POST["file_type"]) : "";

            # 然后检查有效性
            if($filekey == "" || $filename == ""){
                $resp = json_encode(array(
                    "code" => 400,
                    "data" => array(
                        "errmsg" => "Invalid Params, <file_key> and <file_name> cannot be empty"
                    )
                ));
                die($resp);
            }

            # 再写表
            $timenow = time();
            $insertSQL = "INSERT INTO uploads(user_id, file_key, file_name, file_size, file_type, created_at)
                            VALUES('$uid', '$filekey', '$filename', '$filesize', '$filetype', '$timenow')";
            $lastInsertId = $db->insert($insertSQL);

            # 最后返回处理结果
            if ($lastInsertId > 0) {
                die(json_encode(array("code" => 200, "data" => array("success" => true))));
            } else {
                die(json_encode(array("code" => 499, "data" => array("errmsg" => "Insert Failed"))));
            }
            break;

        # 如果是未知操作，返回错误
        default:
            $resp = json_encode(array(
                "code" => 400,
                "data" => array("errmsg" => "Invalid URL, Unknow <action>: $act")
            ));
            die($resp);
    }

    ?>

以上已经是一个比较完整的文件上传并执行相应的回调的操作流程，想必您对整个上传和回调处理流程已经有比较深刻的认知。如果有兴趣，您还可以下载以上样例的源代码进行深入的查阅。

样例程序下载：[https://github.com/qiniu/php5.3-sdk-example](https://github.com/why404/qiniu-s3-php5.3-sdk-example)

如果您需要对上传组件的界面风格和样式进行调整，请编辑 public/assets/js/fileprogress.js 和 public/assets/css/ 目录里边的相应css文件。

祝您使用愉快！:)
