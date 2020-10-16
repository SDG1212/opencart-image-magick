<?php
/**
 * Opencart Image Magick is a program code which allows you to use Image Magick library in CMS Opencart (3.X version).
 *
 * Copyright (C) 2020  Dmitriy Sokolenko
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Image class
 */
class Image {
	private $file;
	private $image;
	private $width;
	private $height;
	private $bits;
	private $mime;

	/**
	 * Constructor
	 *
	 * @param string $file
	 *
	 * @return void
	 */
	public function __construct($file) {
		if (file_exists($file)) {
			$this->file = $file;
			$this->image = new Imagick();
			$this->image->readImage($file);

			$this->width = $this->image->getImageWidth();
			$this->height = $this->image->getImageHeight();
			$this->bits = $this->image->getImageLength();
			$this->mime = $this->image->getFormat();
		} else {
			exit('Error: Could not load image ' . $file . '!');
		}
	}

	/**
	 * @return string
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @return string
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @return string
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @return string
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @return string
	 */
	public function getBits() {
		return $this->bits;
	}

	/**
	 * @return string
	 */
	public function getMime() {
		return $this->mime;
	}

	/**
	 * @param string $file
	 * @param int $quality
	 *
	 * @return void
	 */
	public function save($file, $quality = 100) {
		$this->image->setCompressionQuality($quality);

		$this->image->setImageFormat($this->mime);

		$this->image->writeImage($file);
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @param string $default
	 *
	 * @return void
	 */
	public function resize($width = 0, $height = 0, $default = '') {
		if (!$this->width || !$this->height) {
			return;
		}

		switch ($default) {
			case 'w':
				$height = $width;
				break;
			case 'h':
				$width = $height;
				break;
		}

		$this->image->resizeImage($width, $height, Imagick::FILTER_CATROM, 1, true);

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();

		if ($width == $height && $this->width != $this->height) {
			$image = new Imagick();

			if ($this->mime == 'image/png') {
				$background_color = 'transparent';
			} else {
				$background_color = 'white';
			}

			$image->newImage($width, $height, new ImagickPixel($background_color));
			
			$x = (int)(($width - $this->width) / 2);
			$y = (int)(($height - $this->height) / 2);

			$image->compositeImage($this->image, Imagick::COMPOSITE_OVER, $x, $y);

			$this->image = $image;

			$this->width = $this->image->getImageWidth();
			$this->height = $this->image->getImageHeight();
		}
	}

	/**
	 * @param string $watermark
	 * @param string $position
	 *
	 * @return void
	 */
	public function watermark($watermark, $position = 'bottomright') {
		$watermark = new Imagick($watermark);

		switch ($position) {
			case 'overlay':
				for ($width = 0; $width < $this->width; $width += $watermark->getImageWidth()) {
					for ($height = 0; $height < $this->height; $height += $watermark->getImageHeight()) {
						$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $width, $height);
					}
				}
				break;
			case 'topleft':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, 0);
				break;
			case 'topcenter':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, intval(($this->width - $watermark->getImageWidth()) / 2), 0);
				break;
			case 'topright':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $this->width - $watermark->getImageWidth(), 0);
				break;
			case 'middleleft':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, intval(($this->height - $watermark->getImageHeight()) / 2));
				break;
			case 'middlecenter':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, intval(($this->width - $watermark->getImageWidth()) / 2), intval(($this->height - $watermark->getImageHeight()) / 2));
				break;
			case 'middleright':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $this->width - $watermark->getImageWidth(), intval(($this->height - $watermark->getImageHeight()) / 2));
				break;
			case 'bottomleft':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, $this->height - $watermark->getImageHeight());
				break;
			case 'bottomcenter':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, intval(($this->width - $watermark->getImageWidth()) / 2), $this->height - $watermark->getImageHeight());
				break;
			case 'bottomright':
				$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $this->width - $watermark->getImageWidth(), $this->height - $watermark->getImageHeight());
				break;
		}
	}

	/**
	 * @param int $top_x
	 * @param int $top_y
	 * @param int $bottom_x
	 * @param int $bottom_y
	 *
	 * @return void
	 */
	public function crop($top_x, $top_y, $bottom_x, $bottom_y) {
		$this->image->cropImage($bottom_x - $top_x, $bottom_y - $top_y, $top_x, $top_y);

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();
	}

	/**
	 * @param int $degree
	 * @param string $color
	 *
	 * @return void
	 */
	public function rotate($degree, $color = 'FFFFFF') {
		$this->image->rotateImage($color, $degree);

		$this->width = $this->image->getImageWidth();
		$this->height = $this->image->getImageHeight();
	}
}