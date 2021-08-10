<?php
/**
 * 作者：苏晓晴
 * 作者QQ：3074193836
 * 支持两种获取方式 一种type参数直接跳转 一种type参数直接输出Json数据
 */
header('Content-type: text/json;charset=utf-8');
//如果您的网站启用了https，请将此项置为“true”，如果你的网站未启用 https，建议将此项设置为“false”
define('HTTPS', true);
//引入框架文件
require 'Meting.php';
use Metowolf\Meting;
/************ ↓↓↓↓↓ 如果网易云音乐歌曲获取失效，请将你的放到这里！ ↓↓↓↓↓ ***************/
$netease_cookie = "EVNSM=1.0.0; osver=11; deviceId=bnVsbAllMDpkYzpmZjpkYTpmODplNwllNmU1NTEyNjJiMGM1MTQ2CTIyOTNkYjI2OTRmMDg4NDg%3D; appver=8.2.61; NMDI=Q1NKTQkBDADsh7pcBCK8qgbSN5tUAAAAtazvAQoY74I493qrBeOSK1y976KWF670KVmkRMpPzTYB9JQqYa5g4%2BxJK31Q%2FbEwee4kMxVzFSOqGR2cbdIcVjAhREQW7DLq4lTMkWA%2BVUkLmUgN; MUSIC_U=00F953207D7D8109BC2B3997ED883887130769F3721815A2D4680FA09D51B8C063956B846EFDA797CF193BCFF2D528DD7C0DC329BED24D6DBB03E8633F951D252D906027367A299F1C08BD926DC5ABE423458133A6270D81E626FD27DFF6874346620536A14D6E824529287C109F9557E66422670DAF6F9AD5307C813821A9FD9221F0255426BB5CC5CBDAF133CC3BDF70EB3213F738433B76952A03B99EF67BF3FB07DE3A1B4C1FEEBA6B6708958AC949F8A995BD0985FEBC18009C4F452BBC3631904D74C1703A546B13EBFC55D548C496B9E8E77811A5532F59A87C20D8F2F3A9123AC2E6B1E661263A023CD2E79726D25FA038546EE906184A7106FCA5AD2FC6A1C2CAD990EAB07E0787D9BC97E7F8B1268CD3035AA98C998529398B2280C4A7B3A08BDDE23E0C46AE6B64972B1096D4D844A934CA4D3AB0A19782CA01D02A6AA06480975EB3C4226531A1447BC34295F1248B60CDC78D27C303F299372F33150E3D067E76C1E3456CD9D35A50152135D575886AA41488957FE9A888A3A9D2; ntes_kaola_ad=1; NMCID=vljatq.1627464858230.01.4; versioncode=8002061; mobilename=RedmiK20Pro; URS_APPID=4F768D0FF329351E82FB22AB87699622D883C47ADC85D19B8416DFD7BCD5BD8DB6F0E44087D61EFC06BE92279CD6EEC6; buildver=210721141452; resolution=2296x1080; __csrf=da05a11b61127b9326a970bf38225362; NMTID=00OaKRutU76MrxwQ0PUiyIdqKA1PwgAAAF67Hb_qA; os=android; channel=xiaomi";
/************ ↑↑↑↑↑ 如果网易云音乐歌曲获取失效，请将你的放到这里！ ↑↑↑↑↑ ***************/
/**
 * cookie 获取及使用方法见
 * https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90%E9%97%AE%E9%A2%98
 *
 * 如果还有问题，可以联系源码作者
 **/
if ($_SERVER["REQUEST_METHOD"] == "GET") {
	//参数设置 如果不会改 请不要改 默认就好
	if (!empty($_GET['id']) && !empty($_GET['type']) && !empty($_GET['media'])) {
		$id = $_GET['id'];
		$type = $_GET['type'];
		$media = $_GET['media'];
		echo getMusicInfo($media,$type,$id);
	}
}
//网易云读取COOKIE 以防获取不到音乐
//海外服务器自行设置代理IP 以便获取QQ音乐
function getMusicInfo($media = 'netease', $type ='song', $id = '') {
	$api = new Meting($media);
	//请自行设置代理IP 以便获取QQ音乐音频链接// 如果不需要请填写null 填写代理IP格式为 "IP:端口"
	$proxy=null;
	global $netease_cookie;
	if ($media == 'netease') {
		$api->cookie($netease_cookie);
	}
	if ($media == 'tencent') {
		$api->proxy($proxy);
	}
	$info = array();
	$datas = $api->format(true)->$type($id);
	if ($type == 'url') {
		//type参数为url时 直接获取单曲id直链跳转//
		$datas = $api->format(true)->song($id);
	}
	if ($type == 'search') {
		//增加搜索歌曲支持页数和歌曲数量！//
		$page=$_GET['page'];
		$limit=$_GET['limit'];
		$datas = $api->format(true)->search($id, ['page' => $page,'limit' => $limit]);
	}
	$datas = json_decode($datas,true);
	foreach ( $datas as $keys => $data) {
		$cover = json_decode($api->format(true)->pic($data['pic_id']),true)['url'];
		$url = json_decode($api->format(true)->url($data['id']),true)['url'];
		$lrc = $api->lyric($data['id']);
		$lrc = json_decode($lrc, true);
		$lrc_data=lrctran($lrc['lyric'], $lrc['tlyric']);
		/**
		  *修复网易云音乐防止盗链
        */
		if ($media == 'netease') {
			$url = str_replace('://m7c.', '://m7.', $url);
			$url = str_replace('://m8c.', '://m8.', $url);
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
		if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {
			// 替换链接为HTTPS (支持开关)
			$url = str_replace('http:\/\/', 'https:\/\/', $url);
			$url = str_replace("http://","https://", $url);
		}
		if ($type == 'url') {
			//修复直链跳转问题//
			header("Location:".$url);
		}
		$info[$keys] = array('name' => $data['name'],'url' => $url,'song_id' => $data['id'],'cover' => $cover,'author' => implode(' / ', $data['artist']),'lrc_data' => $lrc_data,'version' => '1.5.10');
	}
	return json_encode($info,320|JSON_PRETTY_PRINT);
}
function lrctrim($lyrics) {
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
function lrctran($lyric, $tlyric) {
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