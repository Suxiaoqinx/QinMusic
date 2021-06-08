<?php
header('Content-type: text/json;charset=utf-8');
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
$datas_playlist=$api->format(true)->playlist($id);
$datas_playlist = json_decode($datas_playlist,true);
$music = array();
foreach ($datas_playlist as $vo) {
//解析Meting.php歌单取出图片链接//
$cover = json_decode($api->format(true)->pic($vo['pic_id']),true)['url'];
//解析Meting.php歌单取出音频链接//
$url = json_decode($api->format(true)->url($vo['url_id']),true)['url'];
$lrc = $api->lyric($vo['lyric_id']);
$lrc = json_decode($lrc, true);
$lrc_data=lrctran($lrc['lyric'], $lrc['tlyric']);
//QQ音乐//
if ($type = 'tencent'){
//防止获取ws格式//
$url = str_replace("//ws","//isure", $url);
}
//网易云音乐//
if ($type == 'netease') {
     $url = str_replace('://m7c.', '://m7.', $url);
     $url = str_replace('://m8c.', '://m8.', $url);
     $url = str_replace('http://m8.', 'https://m9.', $url);
     $url = str_replace('http://m7.', 'https://m9.', $url);
     $url = str_replace('http://m10c.', 'https://m10.', $url);
     $url = str_replace('http://m701c.', 'https://m701.', $url);
     $url = str_replace('http://m801c.', 'https://m801.', $url);
     $url = str_replace('https://other.', 'http://other.', $url); 
}
//百度音乐//
if ($type == 'baidu') {
    $url = str_replace('http://zhangmenshiting.qianqian.com', 'http://gss3.baidu.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu', $url);
}
if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {// 替换链接为 https
    $url = str_replace('http:\/\/', 'https:\/\/', $url);
    $url = str_replace('http://', 'https://', $url);
}
$music[] = array(
    'name'   => $vo['name'],
    'artist' => implode('/',$vo['artist']),
    'url' => $url,
    'cover' => $cover,
    'lrc_data'=> $lrc_data,
    );
}
echo json_encode($music,320);
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
