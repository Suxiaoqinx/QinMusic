# QinMusic
源码框架来自Meting音乐框架
如果遇到什么问题请访问www.toubiec.cn 进行反馈！

将Get.php Meting.php playlist.php上传到根目录

//↓↓↓下面是单曲解析↓↓↓//

然后链接格式为xxx.xxx/Get.php?media=解析的音乐网&id=音乐ID  (输出音频直链)
Json数据输出格式 xxx.xxx/Get.php?media=解析的音乐网&id=音乐ID&type=json (输出Json数据)

//↓↓↓下面是歌单解析↓↓↓//

然后链接格式为xxx.xxx/playlist.php?media=解析的音乐网&id=音乐ID  (输出Json数据)

音乐网选项 netease tencent baidu

2020 4 18

更新网易云cookie读取 如果有VIP会员cookie可以解析VIP歌曲！

2020 4 23 更新歌词非中文获取翻译+原文

2021 5 21 修复网易云无法获取其他格式音频 以及 修改QQ音乐ws获取dl格式

2021 6 8 新增加歌单解析 单独playlist.php文件！！
