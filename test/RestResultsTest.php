<?php
use oat\tao\test\RestTestCase;

class RestResultsTest extends RestTestCase
{
    public function serviceProvider(){
        return array(
            array('taoResults/RestResults')
        );
    }
}