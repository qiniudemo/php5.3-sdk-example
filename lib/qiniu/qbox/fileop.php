<?php

namespace QBox\FileOp;

/**
 * func ImageMogrURL(url string, param string) => (urlImageMogr string)
 */
function ImageMogrURL($url, $param) {
	return $url . '/imageMogr/' . $param;
}

/**
 * func ImagePreviewURL(url string, thumbType int) => (urlImagePreview string)
 */
function ImagePreviewURL($url, $thumbType) {
	return $url . '/imagePreview/' . $thumbType;
}

/**
 * func ImageInfoURL(url string) => (urlImageInfo string)
 */
function ImageInfoURL($url) {
	return $url . '/imageInfo';
}

/*
 * 图像处理接口，生成最终的缩略图预览URL
 */
function ImageMogrifyPreviewURL($src_img_url, $opts){
	return $src_img_url . '?' . mkImageMogrifyParams($opts);
}

/*
 * 图像处理接口，生成图像处理的参数
 * func mkImageMogrifyParams() => string
 * opts = {
 *   "thumbnail": <ImageSizeGeometry>,
 *   "gravity": <GravityType>, =NorthWest, North, NorthEast, West, Center, East, SouthWest, South, SouthEast
 *   "crop": <ImageSizeAndOffsetGeometry>,
 *   "quality": <ImageQuality>,
 *   "rotate": <RotateDegree>,
 *   "format": <DestinationImageFormat>, =jpg, gif, png, tif, etc.
 *   "auto_orient": <TrueOrFalse>
 * }
 */
function mkImageMogrifyParams($opts){
    $keys = array("thumbnail", "gravity", "crop", "quality", "rotate", "format");
    $params_string = "";
    foreach($keys as $key){
        if (isset($opts[$key]) && !empty($opts[$key])) {
            $params_string .= '/' . $key . '/' . $opts[$key];
        }
    }
    if(isset($opts["auto_orient"]) && $opts["auto_orient"] === true){
        $params_string .= "/auto-orient";
    }
    return 'imageMogr' . $params_string;
}
