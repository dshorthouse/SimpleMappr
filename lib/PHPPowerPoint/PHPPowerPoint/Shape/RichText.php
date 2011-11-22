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

/** PHPPowerPoint_Shape_RichText_ITextElement */
require_once 'PHPPowerPoint/Shape/RichText/ITextElement.php';

/** PHPPowerPoint_Shape_RichText_TextElement */
require_once 'PHPPowerPoint/Shape/RichText/TextElement.php';

/** PHPPowerPoint_Shape_RichText_Run */
require_once 'PHPPowerPoint/Shape/RichText/Run.php';

/** PHPPowerPoint_Shape_RichText_Break */
require_once 'PHPPowerPoint/Shape/RichText/Break.php';

/** PHPPowerPoint_Style_Alignment */
require_once 'PHPPowerPoint/Style/Alignment.php';

/**
 * PHPPowerPoint_Shape_RichText
 *
 * @category   PHPPowerPoint
 * @package    PHPPowerPoint_RichText
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 */
class PHPPowerPoint_Shape_RichText extends PHPPowerPoint_Shape implements PHPPowerPoint_IComparable
{
	/**
	 * Rich text elements
	 *
	 * @var PHPPowerPoint_Shape_RichText_ITextElement[]
	 */
	private $_richTextElements;
	
	/**
	 * Alignment
	 * 
	 * @var PHPPowerPoint_Style_Alignment
	 */
	private $_alignment;
	   
    /**
     * Create a new PHPPowerPoint_Shape_RichText instance
     */
    public function __construct()
    {
    	// Initialise variables
    	$this->_richTextElements = array();
    	$this->_alignment = new PHPPowerPoint_Style_Alignment();
    	
    	// Initialize parent
    	parent::__construct();
    }
    
    /**
     * Get alignment
     * 
     * @return PHPPowerPoint_Style_Alignment
     */
    public function getAlignment()
    {
    	return $this->_alignment;
    }
    
    /**
     * Add text
     *
     * @param 	PHPPowerPoint_Shape_RichText_ITextElement		$pText		Rich text element
     * @throws 	Exception
     */
    public function addText(PHPPowerPoint_Shape_RichText_ITextElement $pText = null)
    {
    	$this->_richTextElements[] = $pText;
    }
    
    /**
     * Create text (can not be formatted !)
     *
     * @param 	string	$pText	Text
     * @return	PHPPowerPoint_Shape_RichText_TextElement
     * @throws 	Exception
     */
    public function createText($pText = '')
    {
    	$objText = new PHPPowerPoint_Shape_RichText_TextElement($pText);
    	$this->addText($objText);
    	return $objText;
    }
    
    /**
     * Create break
     *
     * @return	PHPPowerPoint_Shape_RichText_Break
     * @throws 	Exception
     */
    public function createBreak()
    {
    	$objText = new PHPPowerPoint_Shape_RichText_Break();
    	$this->addText($objText);
    	return $objText;
    }
    
    /**
     * Create text run (can be formatted)
     *
     * @param 	string	$pText	Text
     * @return	PHPPowerPoint_Shape_RichText_Run
     * @throws 	Exception
     */
    public function createTextRun($pText = '')
    {
    	$objText = new PHPPowerPoint_Shape_RichText_Run($pText);
    	$this->addText($objText);
    	return $objText;
    }
    
    /**
     * Get plain text
     *
     * @return string
     */
    public function getPlainText()
    {
    	// Return value
    	$returnValue = '';
    	
    	// Loop trough all PHPPowerPoint_Shape_RichText_ITextElement
    	foreach ($this->_richTextElements as $text) {
    		$returnValue .= $text->getText();
    	}
    	
    	// Return
    	return $returnValue;
    }
    
    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString() {
    	return $this->getPlainText();
    }
    
    /**
     * Get Rich Text elements
     *
     * @return PHPPowerPoint_Shape_RichText_ITextElement[]
     */
    public function getRichTextElements()
    {
    	return $this->_richTextElements;
    }
    
    /**
     * Set Rich Text elements
     *
     * @param 	PHPPowerPoint_Shape_RichText_ITextElement[]	$pElements		Array of elements
     * @throws 	Exception
     */
    public function setRichTextElements($pElements = null)
    {
    	if (is_array($pElements)) {
    		$this->_richTextElements = $pElements;
    	} else {
    		throw new Exception("Invalid PHPPowerPoint_Shape_RichText_ITextElement[] array passed.");
    	}
    }
    
	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */	
	public function getHashCode() {
		$hashElements = '';
		foreach ($this->_richTextElements as $element) {
			$hashElements .= $element->getHashCode();
		}
		
    	return md5(
    		  $hashElements
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
			if ($key == '_parent') continue;
			
			if (is_object($value)) {
				$this->$key = clone $value;
			} else {
				$this->$key = $value;
			}
		}
	}
}
