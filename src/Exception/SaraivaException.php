<?php

namespace Saraiva\Framework\Exception;

use Exception;

class SaraivaException extends Exception {
    
    public function canReportToUser() {
        return FALSE;
    }
    
}
