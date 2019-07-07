<?php

namespace Tests\AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\Assembler\LayDeputyshipDtoAssembler;
use AppBundle\v2\Registration\DTO\LayDeputyshipDto;
use PHPUnit\Framework\TestCase;

class LayDeputyshipDtoAssemblerTest extends TestCase
{
    /** @var LayDeputyshipDtoAssembler */
    private $sut;

    /** {@inheritDoc} */
    protected function setUp()
    {
        $this->sut = new LayDeputyshipDtoAssembler();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function assembleFromArrayThrowsExceptionsIfGivenIncompleteData(): void
    {
        $this->sut->assembleFromArray([]);
    }

    /** @test */
    public function assembleFromArrayAssemblesAndReturnsALayDeputyshipDto(): void
    {
        $result = $this->sut->assembleFromArray($this->getInput());

        $this->assertInstanceOf(LayDeputyshipDto::class, $result);
        $this->assertEquals('case', $result->getCaseNumber());
        $this->assertEquals('surname', $result->getClientSurname());
        $this->assertEquals('deputy_no', $result->getDeputyNumber());
        $this->assertEquals('deputy_surname', $result->getDeputySurname());
        $this->assertEquals('deputy_postcode', $result->getDeputyPostcode());
        $this->assertEquals('type_of_rep', $result->getTypeOfReport());
        $this->assertEquals('corref', $result->getCorref());
        $this->assertEquals(true, $result->isNdrEnabled());
    }

    /**
     * @test
     * @dataProvider getNdrVariations
     * @param $ndrValue
     * @param $expected
     */
    public function assembleFromArrayDeterminesIfNdrEnabled($ndrValue, $expected): void
    {
        $input = $this->getInput();
        $input['NDR'] = $ndrValue;

        $result = $this->sut->assembleFromArray($input);
        $this->assertEquals($expected, $result->isNdrEnabled());
    }

    /** @return array */
    public function getNdrVariations(): array
    {
        return [
            ['ndrValue' => 'Y', 'expected' => true],
            ['ndrValue' => 1, 'expected' => true],
            ['ndrValue' => 'N', 'expected' => false],
            ['ndrValue' => 0, 'expected' => false],
            ['ndrValue' => null, 'expected' => false],
            ['ndrValue' => '', 'expected' => false],
        ];
    }

    /** @return array */
    private function getInput(): array
    {
        return [
            'Case' => 'case',
            'Surname' => 'surname',
            'Deputy No' => 'deputy_no',
            'Dep Surname' => 'deputy_surname',
            'Dep Postcode' => 'deputy_postcode',
            'Typeofrep' => 'type_of_rep',
            'Corref' => 'corref',
            'NDR' => 'Y',
            'Not used' => 'not_used',
        ];
    }
}
