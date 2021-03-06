<?php
/*
 *  This file is part of the Quantum Unit Solutions development package.
 *
 *  (c) Quantum Unit Solutions <http://github.com/dmeikle/>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: user
 * Date: 3/19/2017
 * Time: 12:31 AM
 */

namespace Gossamer\Pesedget\Entities;

use Gossamer\Pesedget\Entities\EntityManager;

trait EntityManagerTrait {

    protected $entityManager;

    function getEntityManager() {
        return $this->entityManager;
    }

    function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }


}
