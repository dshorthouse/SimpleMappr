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
 * @package    PHPPowerPoint_Writer_PowerPoint2007
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    0.1.0, 2009-04-27
 */


/** PHPPowerPoint */
require_once 'PHPPowerPoint.php';

/** PHPPowerPoint_HashTable */
require_once 'PHPPowerPoint/HashTable.php';

/** PHPPowerPoint_IComparable */
require_once 'PHPPowerPoint/IComparable.php';

/** PHPPowerPoint_Slide */
require_once 'PHPPowerPoint/Slide.php';

/** PHPPowerPoint_IWriter */
require_once 'PHPPowerPoint/Writer/IWriter.php';

/** PHPPowerPoint_Shared_XMLWriter */
require_once 'PHPPowerPoint/Shared/XMLWriter.php';

/** PHPPowerPoint_Writer_PowerPoint2007_WriterPart */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/WriterPart.php';

/** PHPPowerPoint_Writer_PowerPoint2007_ContentTypes */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/ContentTypes.php';

/** PHPPowerPoint_Writer_PowerPoint2007_DocProps */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/DocProps.php';

/** PHPPowerPoint_Writer_PowerPoint2007_Rels */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/Rels.php';

/** PHPPowerPoint_Writer_PowerPoint2007_Theme */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/Theme.php';

/** PHPPowerPoint_Writer_PowerPoint2007_Presentation */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/Presentation.php';

/** PHPPowerPoint_Writer_PowerPoint2007_Slide */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/Slide.php';

/** PHPPowerPoint_Writer_PowerPoint2007_Drawing */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/Drawing.php';

/** PHPPowerPoint_Writer_PowerPoint2007_LayoutPack */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/LayoutPack.php';

/** PHPPowerPoint_Writer_PowerPoint2007_LayoutPack_Default */
require_once 'PHPPowerPoint/Writer/PowerPoint2007/LayoutPack/Default.php';


/**
 * PHPPowerPoint_Writer_PowerPoint2007
 *
 * @category   PHPPowerPoint
 * @package    PHPPowerPoint_Writer_PowerPoint2007
 * @copyright  Copyright (c) 2009 - 2010 PHPPowerPoint (http://www.codeplex.com/PHPPowerPoint)
 */
class PHPPowerPoint_Writer_PowerPoint2007 implements PHPPowerPoint_Writer_IWriter
{
	/**
	 * Office2003 compatibility
	 *
	 * @var boolean
	 */
	private $_office2003compatibility = false;

	/**
	 * Private writer parts
	 *
	 * @var PHPPowerPoint_Writer_PowerPoint2007_WriterPart[]
	 */
	private $_writerParts;

	/**
	 * Private PHPPowerPoint
	 *
	 * @var PHPPowerPoint
	 */
	private $_presentation;

	/**
	 * Private unique PHPPowerPoint_Worksheet_BaseDrawing HashTable
	 *
	 * @var PHPPowerPoint_HashTable
	 */
	private $_drawingHashTable;

	/**
	 * Use disk caching where possible?
	 *
	 * @var boolean
	 */
	private $_useDiskCaching = false;
	
	/**
	 * Disk caching directory
	 *
	 * @var string
	 */
	private $_diskCachingDirectory;
	
	/**
	 * Layout pack to use
	 * 
	 * @var PHPPowerPoint_Writer_PowerPoint2007_LayoutPack
	 */
	private $_layoutPack;

    /**
     * Create a new PHPPowerPoint_Writer_PowerPoint2007
     *
	 * @param 	PHPPowerPoint	$pPHPPowerPoint
     */
    public function __construct(PHPPowerPoint $pPHPPowerPoint = null)
    {
    	// Assign PHPPowerPoint
		$this->setPHPPowerPoint($pPHPPowerPoint);
		
		// Set up disk caching location
		$this->_diskCachingDirectory = './';
		
		// Set layout pack
		$this->_layoutPack = new PHPPowerPoint_Writer_PowerPoint2007_LayoutPack_Default();

    	// Initialise writer parts
		$this->_writerParts['contenttypes'] 	= new PHPPowerPoint_Writer_PowerPoint2007_ContentTypes();
		$this->_writerParts['docprops'] 		= new PHPPowerPoint_Writer_PowerPoint2007_DocProps();
		$this->_writerParts['rels'] 			= new PHPPowerPoint_Writer_PowerPoint2007_Rels();
		$this->_writerParts['theme'] 			= new PHPPowerPoint_Writer_PowerPoint2007_Theme();
		$this->_writerParts['presentation'] 	= new PHPPowerPoint_Writer_PowerPoint2007_Presentation();
		$this->_writerParts['slide'] 			= new PHPPowerPoint_Writer_PowerPoint2007_Slide();
		$this->_writerParts['drawing'] 			= new PHPPowerPoint_Writer_PowerPoint2007_Drawing();

		// Assign parent IWriter
		foreach ($this->_writerParts as $writer) {
			$writer->setParentWriter($this);
		}

		// Set HashTable variables
		$this->_drawingHashTable 			= new PHPPowerPoint_HashTable();
    }

	/**
	 * Get writer part
	 *
	 * @param 	string 	$pPartName		Writer part name
	 * @return 	PHPPowerPoint_Writer_PowerPoint2007_WriterPart
	 */
	function getWriterPart($pPartName = '') {
		if ($pPartName != '' && isset($this->_writerParts[strtolower($pPartName)])) {
			return $this->_writerParts[strtolower($pPartName)];
		} else {
			return null;
		}
	}

	/**
	 * Save PHPPowerPoint to file
	 *
	 * @param 	string 		$pFileName
	 * @throws 	Exception
	 */
	public function save($pFilename = null)
	{
		if (!is_null($this->_presentation)) {
			// If $pFilename is php://output or php://stdout, make it a temporary file...
			$originalFilename = $pFilename;
			if (strtolower($pFilename) == 'php://output' || strtolower($pFilename) == 'php://stdout') {
				$pFilename = @tempnam('./', 'phppttmp');
				if ($pFilename == '') {
					$pFilename = $originalFilename;
				}
			}

			// Create drawing dictionary
			$this->_drawingHashTable->addFromSource( 			$this->getWriterPart('Drawing')->allDrawings($this->_presentation) 		);

			// Create new ZIP file and open it for writing
			$objZip = new ZipArchive();

			// Try opening the ZIP file
			if ($objZip->open($pFilename, ZIPARCHIVE::OVERWRITE) !== true) {
				if ($objZip->open($pFilename, ZIPARCHIVE::CREATE) !== true) {
					throw new Exception("Could not open " . $pFilename . " for writing.");
				}
			}

			// Add [Content_Types].xml to ZIP file
			$objZip->addFromString('[Content_Types].xml', 			$this->getWriterPart('ContentTypes')->writeContentTypes($this->_presentation));

			// Add relationships to ZIP file
			$objZip->addFromString('_rels/.rels', 						$this->getWriterPart('Rels')->writeRelationships($this->_presentation));
			$objZip->addFromString('ppt/_rels/presentation.xml.rels', 	$this->getWriterPart('Rels')->writePresentationRelationships($this->_presentation));

			// Add document properties to ZIP file
			$objZip->addFromString('docProps/app.xml', 				$this->getWriterPart('DocProps')->writeDocPropsApp($this->_presentation));
			$objZip->addFromString('docProps/core.xml', 			$this->getWriterPart('DocProps')->writeDocPropsCore($this->_presentation));

			// Add theme to ZIP file
			$objZip->addFromString('ppt/theme/theme1.xml', 			$this->getWriterPart('Theme')->writeTheme($this->_presentation));

			// Add slide master to ZIP file
			$masterSlide = $this->getLayoutPack()->getMasterSlide();
			$objZip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', 	$this->getWriterPart('Rels')->writeSlideMasterRelationships());
			$objZip->addFromString('ppt/slideMasters/slideMaster1.xml', 			$masterSlide['body']);
			
			// Add slide layouts to ZIP file
			$slideLayouts = $this->getLayoutPack()->getLayouts();
			for ($i = 0; $i < count($slideLayouts); ++$i) {
				$objZip->addFromString('ppt/slideLayouts/_rels/slideLayout' . ($i + 1) . '.xml.rels', 	$this->getWriterPart('Rels')->writeSlideLayoutRelationships());
				$objZip->addFromString('ppt/slideLayouts/slideLayout' . ($i + 1) . '.xml', 				$slideLayouts[$i]['body']);
			}			
			
			// Add presentation to ZIP file
			$objZip->addFromString('ppt/presentation.xml', 			$this->getWriterPart('Presentation')->writePresentation($this->_presentation));

			// Add slides (drawings, ...)
			for ($i = 0; $i < $this->_presentation->getSlideCount(); ++$i) {
				// Add slide
				$objZip->addFromString('ppt/slides/slide' . ($i + 1) . '.xml', 	$this->getWriterPart('Slide')->writeSlide($this->_presentation->getSlide($i)));
			}
			
			// Add slide relationships (drawings, ...)
			for ($i = 0; $i < $this->_presentation->getSlideCount(); ++$i) {
				// Add relationships
				$objZip->addFromString('ppt/slides/_rels/slide' . ($i + 1) . '.xml.rels', 	$this->getWriterPart('Rels')->writeSlideRelationships($this->_presentation->getSlide($i), ($i + 1)));
			}

			// Add media
			for ($i = 0; $i < $this->getDrawingHashTable()->count(); ++$i) {
				if ($this->getDrawingHashTable()->getByIndex($i) instanceof PHPPowerPoint_Shape_Drawing) {
					$imageContents = null;
					$imagePath = $this->getDrawingHashTable()->getByIndex($i)->getPath();

					if (strpos($imagePath, 'zip://') !== false) {
						$imagePath = substr($imagePath, 6);
						$imagePathSplitted = explode('#', $imagePath);

						$imageZip = new ZipArchive();
						$imageZip->open($imagePathSplitted[0]);
						$imageContents = $imageZip->getFromName($imagePathSplitted[1]);
						$imageZip->close();
						unset($imageZip);
					} else {
						$imageContents = file_get_contents($imagePath);
					}

					$objZip->addFromString('ppt/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
				} else if ($this->getDrawingHashTable()->getByIndex($i) instanceof PHPPowerPoint_Shape_MemoryDrawing) {
					ob_start();
					call_user_func(
						$this->getDrawingHashTable()->getByIndex($i)->getRenderingFunction(),
						$this->getDrawingHashTable()->getByIndex($i)->getImageResource()
					);
					$imageContents = ob_get_contents();
					ob_end_clean();

					$objZip->addFromString('ppt/media/' . str_replace(' ', '_', $this->getDrawingHashTable()->getByIndex($i)->getIndexedFilename()), $imageContents);
				}
			}

			// Close file
			if ($objZip->close() === false) {
				throw new Exception("Could not close zip file $pFilename.");
			}

			// If a temporary file was used, copy it to the correct file stream
			if ($originalFilename != $pFilename) {
				if (copy($pFilename, $originalFilename) === false) {
					throw new Exception("Could not copy temporary zip file $pFilename to $originalFilename.");
				}
				@unlink($pFilename);
			}
		} else {
			throw new Exception("PHPPowerPoint object unassigned.");
		}
	}

	/**
	 * Get PHPPowerPoint object
	 *
	 * @return PHPPowerPoint
	 * @throws Exception
	 */
	public function getPHPPowerPoint() {
		if (!is_null($this->_presentation)) {
			return $this->_presentation;
		} else {
			throw new Exception("No PHPPowerPoint assigned.");
		}
	}

	/**
	 * Get PHPPowerPoint object
	 *
	 * @param 	PHPPowerPoint 	$pPHPPowerPoint	PHPPowerPoint object
	 * @throws	Exception
	 */
	public function setPHPPowerPoint(PHPPowerPoint $pPHPPowerPoint = null) {
		$this->_presentation = $pPHPPowerPoint;
	}

    /**
     * Get PHPPowerPoint_Worksheet_BaseDrawing HashTable
     *
     * @return PHPPowerPoint_HashTable
     */
    public function getDrawingHashTable() {
    	return $this->_drawingHashTable;
    }

    /**
     * Get Office2003 compatibility
     *
     * @return boolean
     */
    public function getOffice2003Compatibility() {
    	return $this->_office2003compatibility;
    }

    /**
     * Set Pre-Calculate Formulas
     *
     * @param boolean $pValue	Office2003 compatibility?
     */
    public function setOffice2003Compatibility($pValue = false) {
    	$this->_office2003compatibility = $pValue;
    }

	/**
	 * Get use disk caching where possible?
	 *
	 * @return boolean
	 */
	public function getUseDiskCaching() {
		return $this->_useDiskCaching;
	}

	/**
	 * Set use disk caching where possible?
	 *
	 * @param 	boolean 	$pValue
	 * @param	string		$pDirectory		Disk caching directory
	 * @throws	Exception	Exception when directory does not exist
	 */
	public function setUseDiskCaching($pValue = false, $pDirectory = null) {
		$this->_useDiskCaching = $pValue;
		
		if (!is_null($pDirectory)) {
    		if (is_dir($pDirectory)) {
    			$this->_diskCachingDirectory = $pDirectory;
    		} else {
    			throw new Exception("Directory does not exist: $pDirectory");
    		}
		}
	}
	    
	/**
	 * Get disk caching directory
	 *
	 * @return string
	 */
	public function getDiskCachingDirectory() {
		return $this->_diskCachingDirectory;
	}
	
	/**
	 * Get layout pack to use
	 *
	 * @return PHPPowerPoint_Writer_PowerPoint2007_LayoutPack
	 */
	public function getLayoutPack() {
		return $this->_layoutPack;
	}
	
	/**
	 * Set layout pack to use
	 *
	 * @param 	PHPPowerPoint_Writer_PowerPoint2007_LayoutPack 	$pValue
	 */
	public function setLayoutPack(PHPPowerPoint_Writer_PowerPoint2007_LayoutPack $pValue = null) {
		$this->_layoutPack = $pValue;
	}
}
