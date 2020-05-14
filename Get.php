<?php
/**
 * 作者:情兰/苏晓晴
 * 作者QQ:3074193836
 * 免费脚本 请勿收费！
 */
define('HTTPS', true);//如果您的网站启用了https，请将此项置为“true”，如果你的网站未启用 https，建议将此项设置为“false”
require_once('Meting.php');
use Metowolf\Meting;
/************ ↓↓↓↓↓ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↓↓↓↓↓ ***************/
$netease_cookie = ''; 
/************ ↑↑↑↑↑ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↑↑↑↑↑ ***************/
$id=$_GET['id'];
$type=$_GET['media'];
//使用网易云 百度 xiami 腾讯//
$api = new Meting($type);
global $netease_cookie;
if ($type == 'netease'){
$api->cookie($netease_cookie);
}
//根据音乐ID解析//
$datas=$api->format(true)->song($id);
$datas = json_decode($datas,true);
$data = $datas[0];
//解析Meting.php取出图片链接//
$cover = json_decode($api->format(true)->pic($data['pic_id']),true)['url'];
//解析Meting.php取出音频链接//
$url = json_decode($api->format(true)->url($data['id']),true)['url'];
$lrc = $api->lyric($data['id']);
$lrc = json_decode($lrc, true);
$lrc_data=lrctran($lrc['lyric'], $lrc['tlyric']);
//$lrc_data = $lrc['lyric'];
//$lrc_data = preg_replace('/\s/', '', $lrc_data);
//QQ音乐//
if ($type = 'tencent'){
//将获取到的链接转换为HTTPS//
$url = str_replace("ws","dl", $url);
}
//网易云音乐//
if ($type == 'netease') {
    $url = str_replace('://m7c', '://m7', $url);
    $url = str_replace('://m8c', '://m8', $url);
    $url = str_replace('http://m8', 'http://m9', $url);
    $url = str_replace('http://m7', 'http://m9', $url);
    $url = str_replace('http://m10', 'http://m10', $url);
}
//百度音乐//
if ($type == 'baidu') {
    $url = str_replace('http://zhangmenshiting.qianqian.com', 'http://gss3.baidu.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu', $url);
}
if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {// 替换链接为 https
    $url = str_replace('http:\/\/', 'https:\/\/', $url);
    $url = str_replace('http://', 'https://', $url);
}
$type_s=$_GET['type'];
switch ($type_s)
{   
//Json格式解析                             
case 'json':
header('Content-type: text/json;charset=utf-8');
$info = array(
	'code' => '200',
    'name' => $data['name'],
    'url' => $url,
    'pic' => $cover,
    'author' => $data['artist'][0],
    'lrc_data' =>$lrc_data
);
echo json_encode($info,320);
break;
//直接跳转
default:
header("Location:".$url);
break;
}
function lrctrim($lyrics)
{
    $result = "";
    $lyrics = explode("\n", $lyrics);
    $data = array();
    foreach ($lyrics as $key => $lyric) {
        preg_match('/\[(\d{2}):(\d{2}[\.:]?\d*)]/', $lyric, $lrcTimes);
        $lrcText = preg_replace('/\[(\d{2}):(\d{2}[\.:]?\d*)]/', '', $lyric);
        if (empty($lrcTimes)) {
            continue;
        }
        $lrcTimes = intval($lrcTimes[1]) * 60000 + intval(floatval($lrcTimes[2]) * 1000);
        $lrcText = preg_replace('/\s\s+/', ' ', $lrcText);
        $lrcText = trim($lrcText);
        $data[] = array($lrcTimes, $key, $lrcText);
    }
    sort($data);
    return $data;
}
function lrctran($lyric, $tlyric)
{
    $lyric = lrctrim($lyric);
    $tlyric = lrctrim($tlyric);
    $len1 = count($lyric);
    $len2 = count($tlyric);
    $result = "";
    for ($i=0,$j=0; $i<$len1&&$j<$len2; $i++) {
        while ($lyric[$i][0]>$tlyric[$j][0]&&$j+1<$len2) {
            $j++;
        }
        if ($lyric[$i][0] == $tlyric[$j][0]) {
            $tlyric[$j][2] = str_replace('/', '', $tlyric[$j][2]);
            if (!empty($tlyric[$j][2])) {
                $lyric[$i][2] .= " ({$tlyric[$j][2]})";
            }
            $j++;
        }
    }
    for ($i=0; $i<$len1; $i++) {
        $t = $lyric[$i][0];
        $result .= sprintf("[%02d:%02d.%03d]%s\n", $t/60000, $t%60000/1000, $t%1000, $lyric[$i][2]);
    }
    return $result;
}
?>
