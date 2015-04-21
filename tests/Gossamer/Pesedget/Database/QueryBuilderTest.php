<?php

/*
 *  This file is part of the Quantum Unit Solutions development package.
 * 
 *  (c) Quantum Unit Solutions <http://github.com/dmeikle/>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace tests\Gossamer\Pesedget\Database;

use Gossamer\Pesedget\Database\QueryBuilder;
use Gossamer\Pesedget\Database\DBConnection;
use Gossamer\Pesedget\Database\EntityManager;
use tests\entities\TaxRate;
use tests\entities\Staff;

/**
 * QueryBuilderTest
 *
 * @author Dave Meikle
 */
class QueryBuilderTest  extends \tests\BaseTest{
    
    /**
     * @group count
     */
    public function testCountQuery() {
        
        $builder = new QueryBuilder(array('dbConnection' => EntityManager::getInstance()->getConnection()));
        $builder->where(array('directive::OFFSET' => '0', 'directive::LIMIT' => '20', 'id' => '10', 'locale' => 'en_US'));
      
        $query = $builder->getQuery(new Staff(), QueryBuilder::GET_COUNT_QUERY);
        die($query);
    }
    
    public function testSaveValue() {
        $builder = new QueryBuilder(array('dbConnection' => EntityManager::getInstance()->getConnection()));
        
        $builder->setBulkInsert($this->getValues());
        echo $builder->getQuery(new TaxRate(), QueryBuilder::SAVE_QUERY);
    }
    
    /**
     * @group bulkcart
     */
    public function testSaveCartArray() {
        $builder = new QueryBuilder(array('dbConnection' => EntityManager::getInstance()->getConnection()));
        
        $builder->setBulkInsert($this->getTaxRateArrayValues());
        echo $builder->getQuery(new TaxRate(), QueryBuilder::SAVE_QUERY);
    }
    
    
    /**
     * @group bulk
     */
    public function testSaveArray() {
        $builder = new QueryBuilder(array('dbConnection' => EntityManager::getInstance()->getConnection()));
        
        $builder->setBulkInsert($this->getStaffArrayValues());
        echo $builder->getQuery(new Staff(), QueryBuilder::SAVE_QUERY);
    }
    
    private function getValues() {
        return array( 'id' => '1', 'States_id' => 1, 'taxRate' => .05);
    }
    private function getStaffArrayValues() {
        return array (
            array (
                'id' => 1,
                'firstname' => 'phpunit'
            ),
            array(
                'id' => 3,
                'firstname' => 'phpunit2'
            ),
            array(
                'id' => 4,
                'firstname' => 'phpunit3'
            )
        );
    }
    private function getTaxRateArrayValues() {
        return array (
            array (
                'States_id' => 1,
                'taxRate' => .05
            ),
            array(
                'States_id' => 3,
                'taxRate' => .05
            ),
            array(
                'States_id' => 4,
                'taxRate' => .05
            )
        );
    }
}
