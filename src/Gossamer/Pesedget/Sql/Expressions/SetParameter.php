<?php

/*
 *  This file is part of the Quantum Unit Solutions development package.
 * 
 *  (c) Quantum Unit Solutions <http://github.com/dmeikle/>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Gossamer\Pesedget\Sql\Expressions;

use Gossamer\Pesedget\Sql\SqlDecorator;
/**
 * SetParameter
 *
 * @author Dave Meikle
 */
class SetParameter extends SqlDecorator {
    
    public function __construct($key, $value) {
        $this->sqlStatement = array($key => $value);
    }
    
    public function __toString() {
        
    }

    public function getParameter() {
        return $this->sqlStatement;
    }

}
