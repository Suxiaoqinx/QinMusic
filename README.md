框架来自Meting音乐 如果遇到什么问题请访问www.toubiec.cn 进行反馈！

将Get.php Meting.php上传到根目录

//↓↓↓下面是单曲解析↓↓↓//

然后链接格式为http://xxx.com/Get.php?id=音乐ID&type=url&media=音乐网选项 (跳转音频直链) 

Json数据输出格式 http://xxx.com/Get.php?id=音乐ID&type=song&media=音乐网选项 (输出Json数据)

//↓↓↓下面是歌单解析↓↓↓//

然后链接格式为http://xxx.com/Get.php?id=音乐ID&type=playlist&media=音乐网选项 (输出Json数据)

//↓↓↓下面是专辑解析↓↓↓//

然后链接格式为http://xxx.com/Get.php?id=音乐ID&type=album&media=音乐网选项 (输出Json数据)

//↓↓↓下面是歌手解析↓↓↓//

然后链接格式为http://xxx.com/Get.php?id=音乐ID&type=artist&media=音乐网选项 (输出Json数据)

//↓↓↓下面是搜索解析↓↓↓//

然后链接格式为http://xxx.com/Get.php?id=歌曲名或者歌手&type=search&media=音乐网选项 (输出Json数据)

音乐网选项 netease tencent

音乐解析选项 song playlist url artist album search

搜索解析新增支持页数和控制输出歌曲数量  &page=页数&$limit=输出歌曲数量

//↓↓↓下面是搜索解析(控制输出歌曲数量和页数)↓↓↓//

然后链接格式为http://xxx.com/Get.php?id=歌曲名或者歌手&type=search&media=音乐网选项&page=页数&$limit=输出歌曲数量 (输出Json数据)
