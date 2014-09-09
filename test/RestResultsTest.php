<?php
require_once dirname(__FILE__) . '/../../tao/test/RestTestCase.php';

class RestResultsTest extends RestTestCase
{
    public function serviceProvider(){
        return array(
            array('taoResults/RestResults')
        );
    }
}
?>