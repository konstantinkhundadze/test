<?php

namespace base\view\helper;

class HeadMeta extends AbstractHelper {

    public $title = '';

    /**
     * Meta tags store
     * @var array 
     */
    protected static $_items = array();

    protected function _render()
    {
        $this->_content .= implode(static::$_separator, static::$_items);
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * <meta name="$name" content="$content">
     * @param string $name meta name OR http-equiv see param $useEquiv
     * @param string $content content
     * @param boolean $useEquiv default false
     * @return self
     */
    public function set($name, $content, $useEquiv = false)
    {
        $metaKey = $useEquiv ? 'http-equiv' : 'name';
        self::$_items[$name] = '<meta '.$metaKey.'="'.$name.'" content="'.$content.'">';
        return $this;
    }

}
