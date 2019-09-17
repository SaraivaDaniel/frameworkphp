<?php

namespace Saraiva\Framework\Exception;

class UnauthorizedException extends SaraivaException {
    
    public function canReportToUser() {
        return TRUE;
    }
    
}