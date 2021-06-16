<?php
/**
 * 作者：苏晓晴
 * 作者QQ：3074193836
 * 发起请求数据，返回json
 */
header('Content-type: text/json;charset=utf-8');
define('HTTPS', true);//如果您的网站启用了https，请将此项置为“true”，如果你的网站未启用 https，建议将此项设置为“false”
require 'Meting.php';
use Metowolf\Meting;
/************ ↓↓↓↓↓ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↓↓↓↓↓ ***************/
$netease_cookie = '';
/************ ↑↑↑↑↑ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↑↑↑↑↑ ***************/
/**
 * cookie 获取及使用方法见
 * https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90%E9%97%AE%E9%A2%98
 *
 * 如果还有问题，可以联系源码作者
 **/
if ($_SERVER["REQUEST_METHOD"] == "GET"){

    if (!empty($_GET['id']) && !empty($_GET['type']) && !empty($_GET['media'])){
        $id = $_GET['id'];
        $type = $_GET['type'];
        $media = $_GET['media'];
        echo getMusicInfo($media,$type,$id);
    }

}
/**
 * @param $url
 * @param string $size
 * @return array 返回是的歌单解析信息的数组
 */
function parseMusicUrl($url,$size="large"){
    
    $url=trim($url);
    //echo $url;
    //如果输入的地址为空，则返回空
    if(empty($url))return;
    $media='netease';$id='';$type='';
    if(strpos($url,'163.com')!==false){
        $media='netease';
        if(preg_match('/playlist\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
        elseif(preg_match('/toplist\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
        elseif(preg_match('/album\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
        elseif(preg_match('/song\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'song');
        elseif(preg_match('/artist\?id=(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
    }
    elseif(strpos($url,'qq.com')!==false){
        $media='tencent';
        if(preg_match('/playlist\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
        elseif(preg_match('/album\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'album');
        elseif(preg_match('/song\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'song');
        elseif(preg_match('/singer\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'artist');
    }
    elseif(strpos($url,'xiami.com')!==false){
        $media='xiami';
        if(preg_match('/collect\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
        elseif(preg_match('/album\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'album');
        elseif(preg_match('/[\/.]\w+\/[songdem]+\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'song');
        elseif(preg_match('/artist\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
        if(!preg_match('/^\d*$/i',$id,$t)){
            $data=curl($url);
            preg_match('/'.$type.'\/(\d+)/i',$data,$id);
            $id=$id[1];
        }
    }
    elseif(strpos($url,'kugou.com')!==false){
        $media='kugou';
        if(preg_match('/special\/single\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
        elseif(preg_match('/#hash\=(\w+)/i',$url,$id))list($id,$type)=array($id[1],'song');
        elseif(preg_match('/album\/[single\/]*(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
        elseif(preg_match('/singer\/[home\/]*(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
    }
    elseif(strpos($url,'baidu.com')!==false){
        $media='baidu';
        if(preg_match('/songlist\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
        elseif(preg_match('/album\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
        elseif(preg_match('/song\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'song');
        elseif(preg_match('/artist\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
    }
    else{//输入的地址不能匹配到上述的第三方音乐平台
        $url = preg_replace('/\//','\\/',$url);
        echo "[hplayer title=\"歌曲名\" author=\"歌手\" url=\"{$url}\" size=\"{$size}\" /]\n";
        return;
    }
    echo "[hplayer media=\"{$media}\" id=\"{$id}\" type=\"{$type}\" size=\"{$size}\" /]\n";
}

function curl($url){
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curl,CURLOPT_TIMEOUT,10);
    curl_setopt($curl,CURLOPT_REFERER,$url);
    $result=curl_exec($curl);
    curl_close($curl);
    return $result;
}

function getMusicInfo($media = 'netease', $type = 'song', $id = ''){
    $api = new Meting($media);
    global $netease_cookie;
    if ($media == 'netease'){
        $api->cookie($netease_cookie);
    }

    $info = array();
    switch ($type){
        //直接获取单曲音乐所有信息
        case 'song':
            $datas = $api->format(true)->song($id);
            $datas = json_decode($datas,true);
            $data = $datas[0];
            $cover = json_decode($api->format(true)->pic($data['pic_id']),true)['url'];
            $url = json_decode($api->format(true)->url($data['id']),true)['url'];
            $lrc = $api->lyric($data['id']);
            $lrc = json_decode($lrc, true);
            $lrc_data=lrctran($lrc['lyric'], $lrc['tlyric']);
            /**
             * 修复网易云音乐防止盗链
             */
            if ($media == 'netease') {
                $url = str_replace('://m7c.', '://m7.', $url);
                $url = str_replace('://m8c.', '://m8.', $url);
                $url = str_replace('http://m8.', 'https://m9.', $url);
                $url = str_replace('http://m7.', 'https://m9.', $url);
                $url = str_replace('http://m10c.', 'https://m10.', $url);
                $url = str_replace('http://m701c.', 'https://m701.', $url);
                $url = str_replace('http://m801c.', 'https://m801.', $url);
                $url = str_replace('https://other.', 'http://other.', $url); 
            }
            /**
             * 修复QQ音乐HTTPS问题
             */
            if ($media == 'tencent') {
                //防止获取ws格式//
                $url = str_replace("//ws","//isure", $url);
            }
            if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {// 替换链接为 https
                $url = str_replace('http:\/\/', 'https:\/\/', $url);
                $url = str_replace("http://","https://", $url);
            }
            $info = array(
                'name' => $data['name'],
                'url' => $url,
                'song_id' => $data['id'],
                'cover' => $cover,
                'author' => $data['artist'][0],
                'lrc_data' =>$lrc_data
            );
            break;
        //直接获取音乐地址并跳转
        case 'url':
            $datas = $api->format(true)->song($id);
            $datas = json_decode($datas,true);
            $data = $datas[0];
            $url = json_decode($api->format(true)->url($data['id']),true)['url'];
            /**
             * 修复网易云音乐防止盗链
             */
            if ($media == 'netease') {
                $url = str_replace('://m7c.', '://m7.', $url);
                $url = str_replace('://m8c.', '://m8.', $url);
                $url = str_replace('http://m8.', 'https://m9.', $url);
                $url = str_replace('http://m7.', 'https://m9.', $url);
                $url = str_replace('http://m10c.', 'https://m10.', $url);
                $url = str_replace('http://m701c.', 'https://m701.', $url);
                $url = str_replace('http://m801c.', 'https://m801.', $url);
                $url = str_replace('https://other.', 'http://other.', $url); 
            }
            /**
             * 修复QQ音乐HTTPS问题
             */
            if ($media == 'tencent') {
                //防止获取ws格式//
                $url = str_replace("//ws","//isure", $url);
            }
            if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {// 替换链接为 https
                $url = str_replace('http:\/\/', 'https:\/\/', $url);
                $url = str_replace("http://","https://", $url);
            }
            header("Location:".$url);
            break;
        //获取歌单Json
        case 'playlist':
            $datas = $api->format(true)->playlist($id);
            $datas = json_decode($datas,true);
            foreach ( $datas as $keys => $data){

            $cover = json_decode($api->format(true)->pic($data['pic_id']),true)['url'];
            $url = json_decode($api->format(true)->url($data['id']),true)['url'];
            $lrc = $api->lyric($data['id']);
            $lrc = json_decode($lrc, true);
            $lrc_data=lrctran($lrc['lyric'], $lrc['tlyric']);
            /**
             * 修复网易云音乐防止盗链
             */
            if ($media == 'netease') {
                $url = str_replace('://m7c.', '://m7.', $url);
                $url = str_replace('://m8c.', '://m8.', $url);
                $url = str_replace('http://m8.', 'https://m9.', $url);
                $url = str_replace('http://m7.', 'https://m9.', $url);
                $url = str_replace('http://m10c.', 'https://m10.', $url);
                $url = str_replace('http://m701c.', 'https://m701.', $url);
                $url = str_replace('http://m801c.', 'https://m801.', $url);
                $url = str_replace('https://other.', 'http://other.', $url); 
            }
            /**
             * 修复QQ音乐HTTPS问题
             */
            if ($media == 'tencent') {
                //防止获取ws格式//
                $url = str_replace("//ws","//isure", $url);
            }
            if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {// 替换链接为 https
                $url = str_replace('http:\/\/', 'https:\/\/', $url);
                $url = str_replace("http://","https://", $url);
            }
                $info[$keys] = array(
                    'name' => $data['name'],
                    'url' => $url,
                    'song_id' => $data['id'],
                    'cover' => $cover,
                    'author' => $data['artist'][0],
                    'lrc_data' =>$lrc_data
                );
            }
            break;
        default:
            $data = "";break;
    }
    return json_encode($info,320|JSON_PRETTY_PRINT);
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