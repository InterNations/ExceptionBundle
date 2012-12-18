<?php
namespace InterNations\Bundle\ExceptionBundle\Value;

class Report
{
    public $throwStatementsFound = 0;

    public $throwStatementsRewritten = 0;

    public $useStatementsFound = 0;

    public $useStatementsRewritten = 0;

    public $useStatementsAdded = 0;

    public $catchStatementsFound = 0;

    public function fileChanged()
    {
        return $this->throwStatementsRewritten > 0
            || $this->useStatementsRewritten > 0
            || $this->useStatementsAdded > 0;
    }
}
