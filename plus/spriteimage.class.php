<?php
/**
 * 拼接多幅图片成为一张图片
 * 参数说明：原图片为文件路径数组，目的图片如果留空，则不保存结果，导出css文件如果留空则不保存css结果
 * 具体参数参考下方code
 *
 * 例子：
 * <code>
 *$param = array(
 *  'srcImages' => '',  // array()  图片地址
 *  'destImages' => '', // string   图片生成地址
 *  'destCss' => '',    // string   css生成地址     
 *  'prefix' => '',     // string   css 前缀
 *  'width' => 200,     // int      每张图片的宽度
 *  'height' => 200     // int      每张图片的高度
 *  'cssPath' => ''     // string   css文件相对于图片的路径
 *  'mode' => true //生成图片的方向 true（横向）  false（竖向）
 *);
 * $ci = new SpriteImage($param);
 * $ci->sprite();    //图片拼接 并保存图片，并生成相应的css文件   
 * </code>
 *
 */
class SpriteImage {
	/**
	 * 原图地址数组
	 */
	private $srcImages;
	/**
	 * 每张图片缩放到这个宽度
	 */
	private $width;
	/**
	 * 每张图片缩放到这个高度
	 */
	private $height;
	/**
	 * 拼接模式，可以选择水平或垂直
	 */
	private $mode;

	/**
	 * 目标图片地址
	 */
	private $destImage;
	 /**
	 * 目标css地址
	 */
	private $destCss;
 
	/**
	 * 临时画布
	 */
	private $canvas;

	/**
	 * 背景图片名称 和 位置描述
	 */
	 private $css;

	/**
	 *css 位置描述
	 */
	 private $prefix;
	 /**
	 *css css相对图片的路径
	 */
	 private $cssPath;
	 
	/**
	 * 构造函数，传入原图地址数组和目标图片地址
	 */
	public function __construct($param) {
		$default = array(
			'srcImages' => '',
			'destImage' => '',
			'destCss' => '', 
			'prefix' => 'sprite',
			'width' => 200,
			'height' => 200,
			'cssPath' => '',
			'mode' => true
		);
		$param = array_merge($default, $param);
		$this->srcImages = $param["srcImages"];
		$this->destImage = $param["destImage"];
		$this->destCss = $param["destCss"];
		$this->prefix = $param["prefix"];
		$this->width = $param["width"];
		$this->height = $param["height"];
		$this->mode = $param["mode"];
		$this->cssPath = $param['cssPath'];
		$this->canvas = NULL;
	}
	/**
	 * 析构函数，销毁类之前执行删除画布
	 */
	public function __destruct() {
		if ($this->canvas != NULL) {
			imagedestroy($this->canvas);
		}
	}

	/**
	 * 合并图片
	 */
	public function sprite() {
		if (empty($this->srcImages) || $this->width === 0 || $this->height === 0) {
			return;
		}
		$this->createCanvas();
		$array_css = array();
		$count = count($this->srcImages);
		for($i = 0; $i < $count; ++$i) {
			$srcImage = $this->srcImages[$i];
			$srcImageInfo = getimagesize($srcImage);
			if ($srcImageInfo === false) {
				continue;
			}

			// 如果能够正确的获取原图的基本信息
			$fileName = pathinfo($srcImage, PATHINFO_FILENAME);
			$srcWidth = $srcImageInfo[0];
			$srcHeight = $srcImageInfo[1];
			$fileType = $srcImageInfo[2];
			if ($fileType == 2) {
				// 原图是 jpg 类型
				$srcImage = imagecreatefromjpeg($srcImage);
			} else if ($fileType == 3) {
				// 原图是 png 类型
				$srcImage = imagecreatefrompng($srcImage);
			} else {
				// 无法识别的类型
				continue;
			}
			// 计算当前原图片应该位于画布的哪个位置
			if ($this->mode) {
				$destX = $i * $this->width;
				$desyY = 0;
				$array_css[$fileName] = $i === 0 ? '0 0' : '-'.$destX.'px 0';
			} else {
				$destX = 0;
				$desyY = $i * $this->height;
				$array_css[$fileName] =  $i === 0 ? '0 0': '0 -'.$desyY.'px';
			}
			imagecopyresampled($this->canvas, $srcImage, $destX, $desyY, 0, 0, $this->width, $this->height, $srcWidth, $srcHeight);
		}
		$this->css = $array_css;
		// 如果有指定目标地址，则输出到文件
		if (!empty($this->destImage)) {
			$this->output();
		}
		if (!empty($this->destCss)) {
			$this->outputCss();
		}
	}

	/**
	 * 创建画布
	 */
	private function createCanvas() {
		$imgCount = count($this->srcImages);
		if ($this->mode) {
			$width = $this->width * $imgCount;
			$height = $this->height;
		} else {
			$width = $this->width;
			$height = $this->height * $imgCount;
		}
		$this->canvas = imagecreatetruecolor($width, $height);
		// 使画布透明
		//$white = imagecolorallocatealpha($this->canvas, 0, 0, 0, 127);
		$white = imagecolorallocate($this->canvas, 0, 0, 0);
		imagefill($this->canvas, 0, 0, $white);
		imagecolortransparent($this->canvas, $white);
	}

	/**
	 * 保存结果到文件
	 */
	private function output() {
		// 获取目标文件的后缀
		$fileType = substr(strrchr($this->destImage, '.'), 1);
		($fileType=='jpg' || $fileType=='jpeg') ? imagejpeg($this->canvas, $this->destImage) : imagepng($this->canvas, $this->destImage);
	}
  
	/**
	 * 输入css到文件
	 */
	private function outputCss() {
		if ($this->cssPath) {
			$imagePath = $this->cssPath.pathinfo($this->destImage , PATHINFO_BASENAME);
		} else  {
			$imagePath = $this->destImage;
		}
		$cssDefault = <<<CSS
[class^='{$this->prefix}-'] {
	background: url('{$imagePath}') no-repeat;
	width: {$this->width}px;
	height: {$this->height}px;
}
CSS;
		$imgPosition = array();
		foreach ($this->css as $imgName => $position) {
			$imgPosition[] = <<<CSS
.{$this->prefix}-{$imgName} {
	background-position: {$position};
}
CSS;
		}
		return file_put_contents($this->destCss, $cssDefault.PHP_EOL.implode(PHP_EOL, $imgPosition)) !== false;
	}
}