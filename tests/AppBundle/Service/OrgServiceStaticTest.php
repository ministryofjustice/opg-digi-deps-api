<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\OrgService;

class OrgServiceStaticTest extends \PHPUnit_Framework_TestCase
{
    public static function parseDateProvider()
    {
        return [
            // d-M-y 20xx
            ['05-Feb-15', '2015-02-05', '20'],
            ['23-May-17', '2017-05-23', '20'],
            ['15-Jul-17', '2017-07-15', '20'],
            ['10-Jul-17', '2017-07-10', '20'],
            ['10-Jul-98', '2098-07-10', '20'],
            ['10-Jul-00', '2000-07-10', '20'],

            // d-M-y 19xx
            ['10-Jul-47', '1947-07-10', '19'],
            ['10-Jul-65', '1965-07-10', '19'],
            ['10-Jul-00', '1900-07-10', '19'],

            //d/m/Y format
            ['20-MAR-2003', '2003-03-20', '20'],
            ['29-MAY-2013', '2013-05-29', '20'],
            ['07-OCT-2016', '2016-10-07', '20'],
            ['07-OCT-1945', '1945-10-07', '20'], //third param ignored if full year is given

            // invalid days
            ['00-MAY-2016', false, '20'],
            ['32-MAY-2016', false, '20'],
            // invalid month
            ['07-xxx-2016', false, '20'],
            ['07-janu-2016', false, '20'],
            ['07-00-2016', false, '20'],
            ['07-01-2016', false, '20'],
            // invalid year
            ['01-JAN-0', false, '20'],
            ['01-JAN-1', false, '20'],
            ['01-JAN-000', false, '20'],
            ['01-JAN-001', false, '20'],
            ['01-JAN-00000', false, '20'],
            ['01-JAN-00001', false, '20'],
        ];
    }

    /**
     * @dataProvider parseDateProvider
     */
    public function testparseDate($in, $expectedYmd, $century)
    {
        $actual = OrgService::parseDate($in, $century);

        $this->assertEquals($expectedYmd, $actual ? $actual->format('Y-m-d'): $actual);
    }
}