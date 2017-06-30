<?php

namespace base\helper\session;

class SessionBase extends SessionAbstract implements SessionInterface {

    public function load($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->{$k} = $v;
            }
        } else {
            $this->_single = true;
            $this->_value = $data;
        }
        return $this;
    }
}
