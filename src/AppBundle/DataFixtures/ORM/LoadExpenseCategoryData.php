<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ExpenseCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExpenseCategoryData implements FixtureInterface
{
    private $data = [
        [1, 'Accommodation costs, eg rent, mortgage, service charges', '', 10],
        [2, 'Care fees or local authority charges for care', '', 20],
        [3, 'Holidays and trips', '', 30],
        [4, 'Household bills, eg water, gas, electricity, phone, council tax', '', 40],
        [5, 'Personal allowance', '', 50],
        [6, 'Professional fees, eg solicitor or accountant fees', '', 60],
        [7, 'New investments, eg buying shares, new bonds', '', 70],
        [8, 'Travel costs, eg bus, train, taxi fares', '', 80],
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            if ($manager->find(ExpenseCategory::class, $data[0])) {
                continue;
            }
            $incomeCategory = new ExpenseCategory();
            $incomeCategory->setId($data[0]);
            $incomeCategory->setName($data[1]);
            $incomeCategory->setCode($data[2]);
            $incomeCategory->setDisplayOrder($data[3]);

            $manager->persist($incomeCategory);
            $manager->flush();
        }
    }
}
