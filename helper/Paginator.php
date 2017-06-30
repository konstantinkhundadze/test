<?php

namespace base\helper;

use base\helper\Request;

class Paginator {

    const DEFAULT_PER_PAGE = 20;

    protected $_request;
    protected $_currentPage;
    protected $_totalRecords;
    protected $_recordsPerPage;
    protected $_pagingPath;
    protected $_totalPages;
    protected $_pageKey = 'page';
    protected $_searchTerm = '';

    /**
     * @param integer $totalRecords
     * @param integer $recordsPerPage self::DEFAULT_PER_PAGE
     * @param string $pagingPath
     */
    public function __construct($totalRecords, $recordsPerPage = self::DEFAULT_PER_PAGE, $pagingPath = '')
    {
        $this->_request = new Request();

        $this->_totalRecords = $totalRecords;
        $this->_recordsPerPage = $recordsPerPage;
        $this->_pagingPath = $pagingPath;

        $this->_currentPage = $this->_request->get($this->_pageKey, 1);

        $this->_totalPages = $this->_totalRecords ? ceil($this->_totalRecords / $this->_recordsPerPage) : 1;

        $this->_parseSearchTerm();
    }

    public function getTotal()
    {
        return $this->_totalRecords;
    }

    public function setPageKey($pageKey)
    {
        $this->_pageKey = $pageKey;
        $this->_currentPage = $this->_request->get($this->_pageKey, 1);
        return $this;
    }

    protected function _parseSearchTerm()
    {
        foreach ($this->_request->getQueryParams() as $key => $val) {
            if ($key == $this->_pageKey) {
                continue;
            }
            $this->_searchTerm .= $key . '=' . $val . '&';
        }

        if ($this->_searchTerm) {
            $this->_searchTerm = '?' . $this->_searchTerm;
        }

        return $this;
    }

    /**
     * @param integer $limit pages limit default 0
     * @param string $pref example: 'Go To'
     * @return string
     */
    public function showNavigation($limit = 0, $pref = '')
    {
        if ($this->_totalPages <= 1) {
            return '';
        }

        $limit = $limit ?: $this->_totalPages;

        $hrefPre = $this->_pagingPath . $this->_searchTerm . ($this->_searchTerm ? '&' : '?') . $this->_pageKey . '=';

        if ($this->_currentPage > 1) {
            $prevlink = $pref . ' <a href="' . $hrefPre . ($this->_currentPage - 1) . '"> &lt;</a>';
        } else {
            $prevlink = $pref . ' &lt;';
        }

        $nextlink = '<a href="' . $hrefPre . ($this->_currentPage + 1) . '">&gt;</a>';

        $plink = '';
        $firstpage = $last_page = 0;

        if ($this->_currentPage < ($limit - 1)) {
            $plink = $prevlink; //'<<prev';
            $firstpage = 1;
            $last_page = $limit;
            $llink = $nextlink;
        } else {
            $plink = $prevlink; //'<<prev';
            $llink = $nextlink; //'next>>';
            $firstpage = $this->_currentPage - 2;
            $last_page = $this->_currentPage + 2;
        }

        if ($last_page > $this->_totalPages) {
            $last_page = $this->_totalPages;
            $firstpage = $this->_totalPages - ($limit - 1);

            if ($firstpage < 1) {
                $firstpage = 1;
            }
        }

        if ($this->_currentPage > $this->_totalPages) {
            $this->_currentPage = $this->_totalPages;
        }

        if ($this->_currentPage == $this->_totalPages) {
            $llink = '';
        }

        if ($this->_currentPage == 1) {
            $plink = '';
        }

        $pagestr = '&nbsp;';

        for ($i = $firstpage; $i <= $last_page; $i++) {
            if ($i == $this->_currentPage) {
                $pagestr .= '' . $i . ' ';
            } else {
                $pagestr .= '<a href="' . $hrefPre . $i . '">' . $i . '</a> ';
            }
            if ($i < $last_page) {
                $pagestr .= '|&nbsp';
            }
        }

        if ($this->_totalRecords <= $this->_recordsPerPage) {
            $pagestr = '';
            $plink = '';
            $llink = '';
        }
        $finalstring = '<p class="paginator">' . $plink . $pagestr . $llink . '</p>';
        return $finalstring;
    }

    /**
     * @todo <form action="">
     * @param type $pref 'Go To'
     * @return string HTML
     */
    public function showGotoForm($pref = 'Go To')
    {
        if ($this->_totalPages <= 1) {
            return '';
        }
        return '
            <form method="get">
                <p>
                    Go To <input type="number" name="page" min="1" max="' . $this->_totalPages . '" style="width:70px" required /> <input type="submit" value="search" />
                </p>
            </form>';
    }

}
