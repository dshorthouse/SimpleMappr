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
 * @package    PHPPowerPoint_Style
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    0.1.0, 2009-04-27
 */


/** PHPPowerPoint_Style_Border */
require_once 'PHPPowerPoint/Style/Border.php';

/** PHPPowerPoint_IComparable */
require_once 'PHPPowerPoint/IComparable.php';


/**
 * PHPPowerPoint_Style_Borders
 *
 * @category   PHPPowerPoint
 * @package    PHPPowerPoint_Style
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 */
class PHPPowerPoint_Style_Borders implements PHPPowerPoint_IComparable
{
	/* Diagonal directions */
	const DIAGONAL_NONE		= 0;
	const DIAGONAL_UP		= 1;
	const DIAGONAL_DOWN		= 2;
	
	/**
	 * Left
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_left;
	
	/**
	 * Right
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_right;
	
	/**
	 * Top
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_top;
	
	/**
	 * Bottom
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_bottom;
	
	/**
	 * Diagonal
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_diagonal;
	
	/**
	 * Vertical
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_vertical;
	
	/**
	 * Horizontal
	 *
	 * @var PHPPowerPoint_Style_Border
	 */
	private $_horizontal;
	
	/**
	 * DiagonalDirection
	 *
	 * @var int
	 */
	private $_diagonalDirection;
	
	/**
	 * Outline, defaults to true
	 *
	 * @var boolean
	 */
	private $_outline;
	
	/**
	 * Parent
	 *
	 * @var PHPPowerPoint_Style
	 */
	 
	private $_parent;
	
	/**
	 * Parent Borders
	 *
	 * @var _parentPropertyName string
	 */
	private $_parentPropertyName;
		
	/**
     * Create a new PHPPowerPoint_Style_Borders
     */
    public function __construct()
    {
    	// Initialise values
    	
		/**
		 * The following properties are late bound. Binding is initiated by property classes when they are modified.
		 *
		 * _left
		 * _right
		 * _top
		 * _bottom
		 * _diagonal
		 * _vertical
		 * _horizontal
		 *
		 */
	
    	$this->_diagonalDirection	= PHPPowerPoint_Style_Borders::DIAGONAL_NONE;
    	$this->_outline				= true;
    }

	/**
	 * Property Prepare bind
	 *
	 * Configures this object for late binding as a property of a parent object
	 *	 
	 * @param $parent
	 * @param $parentPropertyName
	 */
	public function propertyPrepareBind($parent, $parentPropertyName)
	{
		// Initialize parent PHPPowerPoint_Style for late binding. This relationship purposely ends immediately when this object
		// is bound to the PHPPowerPoint_Style object pointed to so as to prevent circular references.
		$this->_parent		 		= $parent;
		$this->_parentPropertyName	= $parentPropertyName;
	}

    /**
     * Property Get Bound
     *
     * Returns the PHPPowerPoint_Style_Borders that is actual bound to PHPPowerPoint_Style
	 *
	 * @return PHPPowerPoint_Style_Borders
     */
	private function propertyGetBound() {
		if(!isset($this->_parent))
			return $this;																// I am bound

		if($this->_parent->propertyIsBound($this->_parentPropertyName))
			return $this->_parent->getBorders();										// Another one is bound

		return $this;																	// No one is bound yet
	}
	
    /**
     * Property Begin Bind
     *
     * If no PHPPowerPoint_Style_Borders has been bound to PHPPowerPoint_Style then bind this one. Return the actual bound one.
	 *
	 * @return PHPPowerPoint_Style_Borders
     */
	private function propertyBeginBind() {
		if(!isset($this->_parent))
			return $this;																// I am already bound

		if($this->_parent->propertyIsBound($this->_parentPropertyName))
			return $this->_parent->getBorders();										// Another one is already bound
			
		$this->_parent->propertyCompleteBind($this, $this->_parentPropertyName);		// Bind myself
		$this->_parent = null;
		
		return $this;
	}
          

    /**
     * Property Complete Bind
     *
     * Complete the binding process a child property object started
	 *
     * @param	$propertyObject
     * @param	$propertyName			Name of this property in the parent object
     */ 
    public function propertyCompleteBind($propertyObject, $propertyName) {
    	switch($propertyName) {
    		case "_left":
				$this->propertyBeginBind()->_left = $propertyObject;
				break;
    			
    		case "_right":
				$this->propertyBeginBind()->_right = $propertyObject;
				break;
    			
    		case "_top":
				$this->propertyBeginBind()->_top = $propertyObject;
				break;
    			
    		case "_bottom":
				$this->propertyBeginBind()->_bottom = $propertyObject;
				break;
    			
			case "_diagonal":
				$this->propertyBeginBind()->_diagonal = $propertyObject;
				break;
				
			case "_vertical":
				$this->propertyBeginBind()->_vertical = $propertyObject;
				break;
			
			case "_horizontal":
				$this->propertyBeginBind()->_horizontal = $propertyObject;
				break;

			default:
				throw new Exception("Invalid property passed.");
    	}
    }

	/**
	 * Property Is Bound
	 *
	 * Determines if a child property is bound to this one
	 *
     * @param	$propertyName			Name of this property in the parent object
	 *
	 * @return boolean
	 */
	public function propertyIsBound($propertyName) {
    	switch($propertyName) {
    		case "_left":
				return isset($this->propertyGetBound()->_left);

    		case "_right":
				return isset($this->propertyGetBound()->_right);
    			
    		case "_top":
				return isset($this->propertyGetBound()->_top);
    			
    		case "_bottom":
				return isset($this->propertyGetBound()->_bottom);
    			
    		case "_diagonal":
				return isset($this->propertyGetBound()->_diagonal);
    			
			case "_vertical":
				return isset($this->propertyGetBound()->_vertical);
				
			case "_horizontal":
				return isset($this->propertyGetBound()->_horizontal);
				
			default:
				throw new Exception("Invalid property passed.");
		}
	}
	
	/**
     * Apply styles from array
     * 
     * <code>
     * $objPHPPowerPoint->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     * 		array(
     * 			'bottom'     => array(
     * 				'style' => PHPPowerPoint_Style_Border::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			),
     * 			'top'     => array(
     * 				'style' => PHPPowerPoint_Style_Border::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			)
     * 		)
     * );
     * </code>
     * <code>
     * $objPHPPowerPoint->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     * 		array(
     * 			'allborders' => array(
     * 				'style' => PHPPowerPoint_Style_Border::BORDER_DASHDOT,
     * 				'color' => array(
     * 					'rgb' => '808080'
     * 				)
     * 			)
     * 		)
     * );
     * </code>
     * 
     * @param	array	$pStyles	Array containing style information
     * @throws	Exception
     */
    public function applyFromArray($pStyles = null) {
        if (is_array($pStyles)) {
            if (array_key_exists('allborders', $pStyles)) {
        		$this->getLeft()->applyFromArray($pStyles['allborders']);
        		$this->getRight()->applyFromArray($pStyles['allborders']);
        		$this->getTop()->applyFromArray($pStyles['allborders']);
        		$this->getBottom()->applyFromArray($pStyles['allborders']);
        	}
        	if (array_key_exists('left', $pStyles)) {
        		$this->getLeft()->applyFromArray($pStyles['left']);
        	}
        	if (array_key_exists('right', $pStyles)) {
        		$this->getRight()->applyFromArray($pStyles['right']);
        	}
        	if (array_key_exists('top', $pStyles)) {
        		$this->getTop()->applyFromArray($pStyles['top']);
        	}
        	if (array_key_exists('bottom', $pStyles)) {
        		$this->getBottom()->applyFromArray($pStyles['bottom']);
        	}
        	if (array_key_exists('diagonal', $pStyles)) {
        		$this->getDiagonal()->applyFromArray($pStyles['diagonal']);
        	}
        	if (array_key_exists('vertical', $pStyles)) {
        		$this->getVertical()->applyFromArray($pStyles['vertical']);
        	}
        	if (array_key_exists('horizontal', $pStyles)) {
        		$this->getHorizontal()->applyFromArray($pStyles['horizontal']);
        	}
        	if (array_key_exists('diagonaldirection', $pStyles)) {
        		$this->setDiagonalDirection($pStyles['diagonaldirection']);
        	}
        	if (array_key_exists('outline', $pStyles)) {
        		$this->setOutline($pStyles['outline']);
        	}
    	} else {
    		throw new Exception("Invalid style array passed.");
    	}
    }
    
    /**
     * Get Left
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getLeft() {
    	$property = $this->propertyGetBound();
		if(isset($property->_left))
			return $property->_left;

		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_left");
		return $property;
    }
    
    /**
     * Get Right
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getRight() {
    	$property = $this->propertyGetBound();
		if(isset($property->_right))
			return $property->_right;


		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_right");
		return $property;
    }
       
    /**
     * Get Top
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getTop() {
    	$property = $this->propertyGetBound();
		if(isset($property->_top))
			return $property->_top;


		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_top");
		return $property;
    }
    
    /**
     * Get Bottom
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getBottom() {
    	$property = $this->propertyGetBound();
		if(isset($property->_bottom))
			return $property->_bottom;

		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_bottom");
		return $property;
    }

    /**
     * Get Diagonal
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getDiagonal() {
    	$property = $this->propertyGetBound();
		if(isset($property->_diagonal))
			return $property->_diagonal;

		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_diagonal");
		return $property;
    }
    
    /**
     * Get Vertical
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getVertical() {
    	$property = $this->propertyGetBound();
		if(isset($property->_vertical))
			return $property->_vertical;

		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_vertical");
		return $property;
    }
    
    /**
     * Get Horizontal
     *
     * @return PHPPowerPoint_Style_Border
     */
    public function getHorizontal() {
    	$property = $this->propertyGetBound();
		if(isset($property->_horizontal))
			return $property->_horizontal;

		$property = new PHPPowerPoint_Style_Border();
		$property->propertyPrepareBind($this, "_horizontal");
		return $property;
    }
    
    /**
     * Get DiagonalDirection
     *
     * @return int
     */
    public function getDiagonalDirection() {
    	return $this->propertyGetBound()->_diagonalDirection;
    }
    
    /**
     * Set DiagonalDirection
     *
     * @param int $pValue
     */
    public function setDiagonalDirection($pValue = PHPPowerPoint_Style_Borders::DIAGONAL_NONE) {
        if ($pValue == '') {
    		$pValue = PHPPowerPoint_Style_Borders::DIAGONAL_NONE;
    	}
    	$this->propertyBeginBind()->_diagonalDirection = $pValue;
    }
    
    /**
     * Get Outline
     *
     * @return boolean
     */
    public function getOutline() {
    	return $this->propertyGetBound()->_outline;
    }
    
    /**
     * Set Outline
     *
     * @param boolean $pValue
     */
    public function setOutline($pValue = true) {
        if ($pValue == '') {
    		$pValue = true;
    	}
    	$this->propertyBeginBind()->_outline = $pValue;
    }
    
	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */	
	public function getHashCode() {
		$property = $this->propertyGetBound();
    	return md5(
    		  $property->getLeft()->getHashCode()
    		. $property->getRight()->getHashCode()
    		. $property->getTop()->getHashCode()
    		. $property->getBottom()->getHashCode()
    		. $property->getDiagonal()->getHashCode()
    		. $property->getVertical()->getHashCode()
    		. $property->getHorizontal()->getHashCode()
    		. $property->getDiagonalDirection()
    		. ($property->getOutline() ? 't' : 'f')
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
