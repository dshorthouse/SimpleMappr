<?php
/**
 * PHPPowerPoint
 *
 * Copyright (c) 2009 - 2010 PHPPowerPoint
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPPowerPoint
 * @package    PHPPowerPoint_Shape
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    0.1.0, 2009-04-27
 */


/** PHPPowerPoint_IComparable */
require_once 'PHPPowerPoint/IComparable.php';

/** PHPPowerPoint_Shape */
require_once 'PHPPowerPoint/Shape.php';

/** PHPPowerPoint_Shape_BaseDrawing */
require_once 'PHPPowerPoint/Shape/BaseDrawing.php';


/**
 * PHPPowerPoint_Shape_MemoryDrawing
 *
 * @category   PHPPowerPoint
 * @package    PHPPowerPoint_Shape
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 */
class PHPPowerPoint_Shape_MemoryDrawing extends PHPPowerPoint_Shape_BaseDrawing implements PHPPowerPoint_IComparable 
{	
	/* Rendering functions */
	const RENDERING_DEFAULT					= 'imagepng';
	const RENDERING_PNG						= 'imagepng';
	const RENDERING_GIF						= 'imagegif';
	const RENDERING_JPEG					= 'imagejpeg';
	
	/* MIME types */
	const MIMETYPE_DEFAULT					= 'image/png';
	const MIMETYPE_PNG						= 'image/png';
	const MIMETYPE_GIF						= 'image/gif';
	const MIMETYPE_JPEG						= 'image/jpeg';
	
	/**
	 * Image resource
	 *
	 * @var resource
	 */
	private $_imageResource;
	
	/**
	 * Rendering function
	 *
	 * @var string
	 */
	private $_renderingFunction;
	
	/**
	 * Mime type
	 *
	 * @var string
	 */
	private $_mimeType;
	
	/**
	 * Unique name
	 *
	 * @var string
	 */
	private $_uniqueName;
	
    /**
     * Create a new PHPPowerPoint_Slide_MemoryDrawing
     */
    public function __construct()
    {
    	// Initialise values
    	$this->_imageResource		= null;
    	$this->_renderingFunction 	= self::RENDERING_DEFAULT;
    	$this->_mimeType			= self::MIMETYPE_DEFAULT;
    	$this->_uniqueName			= md5(rand(0, 9999). time() . rand(0, 9999));
    	
    	// Initialize parent
    	parent::__construct();
    }
    
    /**
     * Get image resource
     *
     * @return resource
     */
    public function getImageResource() {
    	return $this->_imageResource;
    }
    
    /**
     * Set image resource
     *
     * @param	$value resource
     */
    public function setImageResource($value = null) {
    	$this->_imageResource = $value;
    	
    	if (!is_null($this->_imageResource)) {
	    	// Get width/height
	    	$this->_width	= imagesx($this->_imageResource);
	    	$this->_height	= imagesy($this->_imageResource);
    	}
    }
    
    /**
     * Get rendering function
     *
     * @return string
     */
    public function getRenderingFunction() {
    	return $this->_renderingFunction;
    }
    
    /**
     * Set rendering function
     *
     * @param string $value
     */
    public function setRenderingFunction($value = PHPPowerPoint_Slide_MemoryDrawing::RENDERING_DEFAULT) {
    	$this->_renderingFunction = $value;
    }
    
    /**
     * Get mime type
     *
     * @return string
     */
    public function getMimeType() {
    	return $this->_mimeType;
    }
    
    /**
     * Set mime type
     *
     * @param string $value
     */
    public function setMimeType($value = PHPPowerPoint_Slide_MemoryDrawing::MIMETYPE_DEFAULT) {
    	$this->_mimeType = $value;
    }
    
    /**
     * Get indexed filename (using image index)
     *
     * @return string
     */
    public function getIndexedFilename() {
		$extension 	= strtolower($this->getMimeType());
		$extension 	= explode('/', $extension);
		$extension 	= $extension[1];
					
    	return $this->_uniqueName . $this->getImageIndex() . '.' . $extension;
    }

	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */	
	public function getHashCode() {
    	return md5(
    		  $this->_renderingFunction
    		. $this->_mimeType
    		. $this->_uniqueName
    		. parent::getHashCode()
    		. __CLASS__
    	);
    }
    
    /**
     * Hash index
     *
     * @var string
     */
    private $_hashIndex;
    
	/**
	 * Get hash index
	 * 
	 * Note that this index may vary during script execution! Only reliable moment is
	 * while doing a write of a workbook and when changes are not allowed.
	 *
	 * @return string	Hash index
	 */
	public function getHashIndex() {
		return $this->_hashIndex;
	}
	
	/**
	 * Set hash index
	 * 
	 * Note that this index may vary during script execution! Only reliable moment is
	 * while doing a write of a workbook and when changes are not allowed.
	 *
	 * @param string	$value	Hash index
	 */
	public function setHashIndex($value) {
		$this->_hashIndex = $value;
	}
        
	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		$vars = get_object_vars($this);
		foreach ($vars as $key => $value) {
			if (is_object($value)) {
				$this->$key = clone $value;
			} else {
				$this->$key = $value;
			}
		}
	}
}
