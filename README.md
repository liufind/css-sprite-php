# css-sprite-php
##php合并图片并生成相应的css

##拼接多幅图片成为一张图片

参数说明：_原图片为文件路径数组，目的图片如果留空，则不保存结果，导出css文件如果留空则不保存css结果_

具体参数参考下方code
 
  例子：
 ~~~
 $param = array(
   'srcImages' => '',  // array()  图片地址
   'destImages' => '', // string   图片生成地址
   'destCss' => '',    // string   css生成地址     
   'prefix' => '',     // string   css 前缀
   'width' => 200,     // int      每张图片的宽度
   'height' => 200     // int      每张图片的高度
   'cssPath' => ''     // string   css文件相对于图片的路径
   'mode' => true //生成图片的方向 true（横向）  false（竖向）
 );
  $ci = new SpriteImage($param);
  $ci->sprite();    //图片拼接 并保存图片，并生成相应的css文件   
~~~
**局限性**
1.拼接的图片长度和宽度都是统一的，适合数量较多，大小统一的图片
2.白色背景的图片会变成透明
