<?php
namespace SimpleMappr;

class HiddenFilterIterator extends \RecursiveFilterIterator
{

    /**
     * This RecursiveFilterIterator works only with the more concrete RecursiveDirectoryIterator type.
     *
     * @param RecursiveDirectoryIterator $iterator
     */
    public function __construct(\RecursiveDirectoryIterator $iterator)
    {
        parent::__construct($iterator);
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @link http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        /** @var RecursiveDirectoryIterator $this PHP SPL RFI/RII does decorate */
        $current = $this->getBasename();

        return strlen($current) && $current[0] !== ".";
    }

    /**
     * By default, PHP does not filter hasChildren(), so this needs to be added.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return parent::hasChildren() && $this->accept();
    }
}