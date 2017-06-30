<?php

namespace base\view\helper;

abstract class AbstractHelper implements HelperInterface {

    /**
     * HTML contrent
     * @var string 
     */
    protected $_content = '';

    /**
     * What text to append the placeholder with when rendering
     *
     * @var string
     */
    protected $_postfix = '';

    /**
     * What text to prefix the placeholder with when rendering
     *
     * @var string
     */
    protected $_prefix = '';

    /**
     * Flag whether to automatically escape output, must also be
     * enforced in the child class if __toString/toString is overridden
     *
     * @var bool
     */
    protected $autoEscape = true;

    /**
     * @var Escaper[]
     */
    protected $escapers = array();
    
    protected static $_separator = PHP_EOL;

    /**
     * Parse data to content, before show
     */
    abstract protected function _render();

    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * String representation
     *
     * @return string
     */
    public function toString()
    {
        return $this->_render()->
                _prefix
                . static::$_separator
                . $this->_content
                . static::$_separator
                . $this->_postfix;
    }

    /**
     * Escape a string
     *
     * @param  string $string
     * @return string
     */
    protected function escape($string)
    {
        return $this->getEscaper()->escapeHtml((string) $string);
    }

    /**
     * Set whether or not auto escaping should be used
     *
     * @param  bool $autoEscape whether or not to auto escape output
     * @return AbstractStandalone
     */
    public function setAutoEscape($autoEscape = true)
    {
        $this->autoEscape = ($autoEscape) ? true : false;
        return $this;
    }

    /**
     * Return whether autoEscaping is enabled or disabled
     *
     * return bool
     */
    public function getAutoEscape()
    {
        return $this->autoEscape;
    }

    /**
     * Set Escaper instance
     *
     * @param  Escaper $escaper
     * @return AbstractStandalone
     */
    public function setEscaper(Escaper $escaper)
    {
        $encoding = $escaper->getEncoding();
        $this->escapers[$encoding] = $escaper;

        return $this;
    }

    /**
     * Get Escaper instance
     *
     * Lazy-loads one if none available
     *
     * @param  string|null $enc Encoding to use
     * @return mixed
     */
    public function getEscaper($enc = 'UTF-8')
    {
        $enc = strtolower($enc);
        if (!isset($this->escapers[$enc])) {
            $this->setEscaper(new Escaper($enc));
        }
        return $this->escapers[$enc];
    }

}
