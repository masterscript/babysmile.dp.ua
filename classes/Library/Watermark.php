<?php

class _Watermark {

    /**
	 * @var array
	 */
	private $watermarks;

	/**
	 * @param string|boolean $white
	 * @param string|boolean $black
	 */
	public function __construct($white=false,$black=false) {


        if (!$white)
            $white = SYSTEM_PATH.'/classes/Library/data/wm_white.png';

        if (!$black)
            $black = SYSTEM_PATH.'/classes/Library/data/wm_black.png';

		$this->watermarks['black'] = new Imagick($black);
		$this->watermarks['white'] = new Imagick($white);

	}

	/**
	 * Draws watermark over the source image
	 * and returns modified Imagick object.
	 * @param string $image_src
	 * @return IMagick
	 */
	public function drawWatermark($image_src) {

		// Create Imagick object from given image path
		$image = new Imagick($image_src);

		// Loop by image's frames
		foreach ($image as $frame) {

			// Detect actual watermark
	        $lumin = new Luminosity($image);
			$watermark = $this->watermarks[$lumin->getInvColor()];

	        // Getting images' geometry
	        $image_width = $frame->getImageWidth();
	        $image_height = $frame->getImageHeight();
	        $watermark_width = $watermark->getImageWidth();
	        $watermark_height = $watermark->getImageHeight();

	        // Calculates aspect ratio of watermark
	        $ratio = min(array($image_width/$watermark_width,$image_height/$watermark_height));

	        // If watermark bigger then source image
	        if ($ratio<1) {
	        	$watermark->thumbnailImage(ceil($watermark_width*$ratio),ceil($watermark_height*$ratio));
	        }

	        // Draw the watermark in center of source image
	        $frame->compositeImage($watermark,
	        					   Imagick::COMPOSITE_OVER,0,
	        					   ceil(($image_height-$watermark->getImageHeight())/2)
	        					   );

		}

        return $image;

	}

}

class Luminosity {

	/**
	 * @var Imagick
	 */
	private $image;

	/**
	 * @param Imagick $image
	 */
	public function __construct(Imagick $image) {

		$this->image = $image;

	}

	/**
	 * Returns color in string representation:
	 * ("white" or "black") reversing to image color map.
	 * @param integer|boolean $region_w width of analizing image region (if "false" - apply image width)
	 * @param integer|boolean $region_h height of analizing image region (if "false" - apply image width)
	 * @return string
	 */
	public function getInvColor($region_w=false,$region_h=false) {

		if (!$region_w) $region_w = $this->image->getImageWidth();
		if (!$region_h) $region_h = $this->image->getImageHeight();

		// Get a region pixel iterator
		$it = $this->image->getPixelRegionIterator(0,0,$region_w,$region_h);
		$luminosity = 0;
		$i = 0;

		// Loop trough rows
		while ($row = $it->getNextIteratorRow()) {

				// Loop trough each column on the row
				foreach ( $row as $pixel ) {

					// Get HSL values
					$hsl = $pixel->getHSL();
					$luminosity += $hsl['luminosity'];
					$i++;

				}

		}

		return (($luminosity/$i) > 0.5) ? "black" : "white" ;

	}

}

