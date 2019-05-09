<?php

namespace App\Exceptions;

class GenerateExcelException extends \Exception
{
    /**
     * GenerateExcelException constructor.
     */
    public function __construct(\Exception $exception)
    {
        parent::__construct('Failed to generate excel. '.$exception->getMessage(), $exception->getCode(), $exception);
    }
}
