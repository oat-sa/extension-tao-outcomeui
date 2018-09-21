<?php

use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;

return new ResultServiceWrapper(
    [
        'class' => \oat\taoOutcomeUi\model\ResultsService::class,
        ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION => 20
    ]
);