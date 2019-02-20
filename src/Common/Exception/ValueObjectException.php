<?php

namespace Webravo\Common\Exception;

use \Exception;

class ValueObjectException extends Exception {

    /**
     * @var array
     */
    protected $errors;

    public function __construct($message='', $code=0, $previous=null, $a_errors=[])
    {
        $this->errors = $a_errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get errors bind to exception
     *
     * @return Array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}