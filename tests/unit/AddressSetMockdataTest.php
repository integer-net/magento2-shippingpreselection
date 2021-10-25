<?php

namespace IntegerNet\ShippingPreselection;

use BKubicki\Magento2TestDoubles\Quote\Api\Data\QuoteAddressStubBuilder;
use IntegerNet\ShippingPreselection\Service\AddressSetMockdata;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\AddressInterface;
use PHPUnit\Framework\TestCase;

class AddressSetMockdataTest extends TestCase
{
    private AddressSetMockdata $addressSetMockdata;
    /**
     * @var ScopeConfigInterface&\PHPUnit\Framework\MockObject\Stub
     */
    private ScopeConfigInterface $configStub;

    protected function setUp(): void
    {
        $this->configStub = $this->createStub(ScopeConfigInterface::class);
        $this->addressSetMockdata = new AddressSetMockdata($this->configStub);
    }

    /**
     * @test
     * @dataProvider dataAddress
     */
    public function uses_existing_address_data(array $existingAddressData)
    {
        $address = QuoteAddressStubBuilder::addressStub()->withData($existingAddressData)->build();
        $this->addressSetMockdata->setMockDataOnAddress($address);
        $this->assertEquals(
            $existingAddressData,
            [
                AddressInterface::KEY_FIRSTNAME  => $address->getFirstname(),
                AddressInterface::KEY_LASTNAME   => $address->getLastname(),
                AddressInterface::KEY_POSTCODE   => $address->getPostcode(),
                AddressInterface::KEY_CITY       => $address->getCity(),
                AddressInterface::KEY_TELEPHONE  => $address->getTelephone(),
                AddressInterface::KEY_REGION_ID  => $address->getRegionId(),
                AddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
                AddressInterface::KEY_STREET     => $address->getStreet(),
            ],
            'Address data should be unchanged'
        );
    }

    /**
     * @test
     * @dataProvider dataEmptyAddress
     */
    public function mocks_empty_data(array $emptyAddressData)
    {
        $mockStringValue = '__PREFILL__';
        $this->configStub->method('getValue')->willReturnMap(
            [
                [AddressSetMockdata::CONFIG_PATH_MOCK_DATASET, 'store', null, $mockStringValue],
                [AddressSetMockdata::CONFIG_PATH_DEFAULT_COUNTRY_ID, 'store', null, 'DE'],
                [AddressSetMockdata::CONFIG_PATH_DEFAULT_REGION_ID, 'store', null, 89],
                [AddressSetMockdata::CONFIG_PATH_DEFAULT_POSTCODE, 'store', null, '12345'],
            ],
        );
        $address = QuoteAddressStubBuilder::addressStub()->withData($emptyAddressData)->build();
        $this->addressSetMockdata->setMockDataOnAddress($address);
        $expectedMockdata = [
            AddressInterface::KEY_FIRSTNAME  => $mockStringValue,
            AddressInterface::KEY_LASTNAME   => $mockStringValue,
            AddressInterface::KEY_POSTCODE   => '12345',
            AddressInterface::KEY_CITY       => $mockStringValue,
            AddressInterface::KEY_TELEPHONE  => $mockStringValue,
            AddressInterface::KEY_REGION_ID  => 89,
            AddressInterface::KEY_COUNTRY_ID => 'DE',
            AddressInterface::KEY_STREET     => [$mockStringValue],
        ];
        $this->assertEquals(
            $expectedMockdata,
            [
                AddressInterface::KEY_FIRSTNAME  => $address->getFirstname(),
                AddressInterface::KEY_LASTNAME   => $address->getLastname(),
                AddressInterface::KEY_POSTCODE   => $address->getPostcode(),
                AddressInterface::KEY_CITY       => $address->getCity(),
                AddressInterface::KEY_TELEPHONE  => $address->getTelephone(),
                AddressInterface::KEY_REGION_ID  => $address->getRegionId(),
                AddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
                AddressInterface::KEY_STREET     => $address->getStreet(),
            ],
            'Address data should be mocked'
        );
    }

    public function dataAddress()
    {
        yield 'address with single line street' => [
            [
                AddressInterface::KEY_FIRSTNAME  => 'Mickey',
                AddressInterface::KEY_LASTNAME   => 'Mouse',
                AddressInterface::KEY_POSTCODE   => '75858',
                AddressInterface::KEY_CITY       => 'Mouseton',
                AddressInterface::KEY_TELEPHONE  => '555-666',
                AddressInterface::KEY_REGION_ID  => 1,
                AddressInterface::KEY_COUNTRY_ID => 'US',
                AddressInterface::KEY_STREET     => 'Mouse Street 1',
            ],
        ];
        yield 'address with multi-line street' => [
            [
                AddressInterface::KEY_FIRSTNAME  => 'Mickey',
                AddressInterface::KEY_LASTNAME   => 'Mouse',
                AddressInterface::KEY_POSTCODE   => '75858',
                AddressInterface::KEY_CITY       => 'Mouseton',
                AddressInterface::KEY_TELEPHONE  => '555-666',
                AddressInterface::KEY_REGION_ID  => 1,
                AddressInterface::KEY_COUNTRY_ID => 'US',
                AddressInterface::KEY_STREET     => ['Mouse Street 1', 'Apt 1'],
            ],
        ];
    }

    public function dataEmptyAddress()
    {
        yield [
            [
                AddressInterface::KEY_FIRSTNAME  => '',
                AddressInterface::KEY_LASTNAME   => '',
                AddressInterface::KEY_POSTCODE   => '',
                AddressInterface::KEY_CITY       => '',
                AddressInterface::KEY_TELEPHONE  => '',
                AddressInterface::KEY_REGION_ID  => null,
                AddressInterface::KEY_COUNTRY_ID => '',
                AddressInterface::KEY_STREET     => [],
            ],
        ];
    }
}
