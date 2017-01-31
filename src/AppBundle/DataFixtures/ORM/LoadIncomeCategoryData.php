<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IncomeCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIncomeCategoryData implements FixtureInterface
{
    private $data = [
        [1, 'State pension and benefits', '', 10],
        [2, 'Bequests, eg inheritance, gifts received', '', 20],
        [3, 'Income from investments, dividends, property rental', '', 30],
        [4, 'Sale of investments, property or assets', '', 40],
        [5, 'Salary or wages', '', 50],
        [6, 'Compensations and damages awards', '', 60],
        [7, 'Personal pension', '', 70],
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            if ($manager->find(IncomeCategory::class, $data[0])) {
                continue;
            }
            $incomeCategory = new IncomeCategory();
            $incomeCategory->setId($data[0]);
            $incomeCategory->setName($data[1]);
            $incomeCategory->setCode($data[2]);
            $incomeCategory->setDisplayOrder($data[3]);

            $manager->persist($incomeCategory);
            $manager->flush();
        }
    }
}
