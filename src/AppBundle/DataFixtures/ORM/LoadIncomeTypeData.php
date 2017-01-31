<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IncomeCategory;
use AppBundle\Entity\IncomeType;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIncomeTypeData implements FixtureInterface
{
    private $data = [
        [1, 1, 'Employment Support Allowance/Incapacity Benefit', '', 10],
        [2, 1, 'Income Support/Pension Guarantee Credit', '', 20],
        [3, 1, 'Income-related Employment and Support Allowance', '', 30],
        [4, 1, 'Income-based Job Seeker’s Allowance', '', 40],
        [5, 1, 'Housing Benefit', '', 50],
        [6, 1, 'Severe Disablement Allowance', '', 60],
        [7, 1, 'Disability Living Allowance', '', 70],
        [8, 1, 'Attendance Allowance', '', 80],
        [9, 1, 'State Pension', '', 90],
        [10, 1, 'Personal Independence Payment', '', 100],
        [11, 1, 'Universal Credit', '', 110],
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            if ($manager->find(IncomeType::class, $data[0])) {
                continue;
            }
            $incomeCategory = $manager->find(IncomeCategory::class, $data[1]);
            $incomeType = new IncomeType();
            $incomeType->setId($data[0]);
            $incomeType->setIncomeCategory($incomeCategory);
            $incomeType->setName($data[2]);
            $incomeType->setCode($data[3]);
            $incomeType->setDisplayOrder($data[4]);

            $manager->persist($incomeType);
            $manager->flush();
        }
    }
}
