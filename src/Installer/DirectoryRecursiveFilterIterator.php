<?php
/**
 * Created by PhpStorm.
 * User: aurimas
 * Date: 5/13/16
 * Time: 3:40 PM
 */

namespace OxidEsales\ComposerPlugin\Installer;


use RecursiveFilterIterator;
use RecursiveIterator;

class DirectoryRecursiveFilterIterator extends RecursiveFilterIterator
{
    /** @var array */
    private $directoriesToSkip = [];

    /**
     * DirectoryRecursiveFilterIterator constructor.
     *
     * @param RecursiveIterator $iterator
     * @param                   $directoriesToSkip
     */
    public function __construct(RecursiveIterator $iterator, $directoriesToSkip)
    {
        $this->directoriesToSkip = $directoriesToSkip;
        parent::__construct($iterator);
    }

    /**
     * If directory start matches the one from provided array, it will be skipped
     *
     * @return bool
     */
    public function accept()
    {
        foreach ($this->directoriesToSkip as $skip) {
            $skip = preg_quote(trim($skip, '/'), '/');
            if (preg_match("/^${skip}(\\/|$)/", $this->current()->getPathName())) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return DirectoryRecursiveFilterIterator
     */
    public function getChildren()
    {
        return new self($this->getInnerIterator()->getChildren(), $this->directoriesToSkip);
    }
}
