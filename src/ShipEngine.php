<?php
/**
 * PHP Implementation for ShipEngine API
 *
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is found in the root folder of
 * this source code package.
 *
 * @author    John Hall
 */
namespace jsamhall\ShipEngine;

/**
 * Class ShipEngine
 *
 * @package ShipEngine
 */
class ShipEngine
{
    /**
     * Request Factory
     *
     * @var Api\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var Address\Factory
     */
    protected $addressFactory;

    /**
     * ShipEngine constructor.
     *
     * @param string          $apiKey
     * @param Address\Factory $addressFactory
     */
    public function __construct(string $apiKey, Address\Factory $addressFactory)
    {
        $this->requestFactory = new Api\RequestFactory($apiKey);
        $this->addressFactory = $addressFactory;
    }

    /**
     * Validates the given Addresses against ShipEngine's Address Validator
     *
     * @link https://docs.shipengine.com/docs/address-validation
     *
     * @param array $addresses Array of Address\Address or domain Addresses which are passed through the Formatter
     *
     * @return AddressVerification\VerificationResult[] Array of Verification Results for every address requested
     */
    public function validateAddresses(array $addresses)
    {
        $addressData = array_map(function ($address) {
            return $address instanceof Address\Address
                ? $address->toArray()
                : $this->addressFactory->factory($address)->toArray();
        }, $addresses);

        $response = $this->requestFactory->validateAddresses($addressData)->send();

        return array_map(function ($data) {
            return new AddressVerification\VerificationResult($data);
        }, $response->getData());
    }

    /**
     * Lists all Carriers setup in the ShipEngine account
     *
     * @link https://docs.shipengine.com/docs/list-your-carriers
     *
     * @return Carriers\Carrier[]
     */
    public function listCarriers()
    {
        $response = $this->requestFactory->listCarriers()->send();

        return array_map(function ($carrier) {
            return new Carriers\Carrier($carrier);
        }, $response->getData('carriers'));
    }

    /**
     * Get a single Carrier
     *
     * @param string $carrierId
     * @return Carriers\Carrier
     */
    public function getCarrier(string $carrierId)
    {
        $response = $this->requestFactory->getCarrier($carrierId)->send();

        return new Carriers\Carrier($response->getData());
    }

    /**
     * List all Services offered by a Carrier
     *
     * @param string $carrierId
     * @return Carriers\Service[]
     */
    public function listCarrierServices(string $carrierId)
    {
        $response = $this->requestFactory->listCarrierServices($carrierId)->send();

        return array_map(function ($carrier) {
            return new Carriers\Service($carrier);
        }, $response->getData('services'));
    }

    /**
     * List all Package Types offered by a Carrier
     *
     * @param string $carrierId
     * @return Carriers\PackageType[]
     */
    public function listCarrierPackageTypes(string $carrierId)
    {
        $response = $this->requestFactory->listCarrierPackageTypes($carrierId)->send();

        return array_map(function ($carrier) {
            return new Carriers\PackageType($carrier);
        }, $response->getData('packages'));
    }

    /**
     * Get all Options offered by a Carrier
     *
     * @param string $carrierId
     * @return Carriers\Option[]
     */
    public function getCarrierOptions(string $carrierId)
    {
        $response = $this->requestFactory->getCarrierOptions($carrierId)->send();

        return array_map(function ($carrier) {
            return new Carriers\Option($carrier);
        }, $response->getData('options'));
    }

    /**
     * Get all Rates for the given Shipment and RateOptions
     *
     * @param Rating\Shipment $shipment
     * @param Rating\Options  $rateOptions
     * @return Api\Response|Rating\RateResponse
     */
    public function getRates(Rating\Shipment $shipment, Rating\Options $rateOptions)
    {
        if (! count($rateOptions)) {
            throw new \InvalidArgumentException("\$rateOptions must include at least one Carrier");
        }

        $response = $this->requestFactory->getShipmentRates($shipment, $rateOptions)->send();

        return new Rating\RateResponse($response->getData('rate_response'));
    }

    public function createLabel(Labels\Shipment $shipment, $testMode = false)
    {
        $response = $this->requestFactory->createLabel($shipment, $testMode)->send();

        return new Labels\Response($response->getData());
    }
}