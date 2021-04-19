<?php

namespace App\Model\Modflow\Boundary;

use Assert\Assertion;
use Assert\AssertionFailedException;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;

class BoundaryFactory
{
    protected string $id;

    /**
     * @param array $arr
     * @return BoundaryInterface
     * @throws AssertionFailedException
     */
    public static function fromArray(array $arr): ?BoundaryInterface
    {

        $geoJson = GeoJson::jsonUnserialize($arr);

        if ($geoJson instanceof Feature) {
            Assertion::keyExists($geoJson->getProperties(), 'type');
            $type = $geoJson->getProperties()['type'];

            switch ($type) {
                case 'hob':
                    return HeadObservationWell::fromArray($arr);
                case 'evt':
                    return EvapotranspirationBoundary::fromArray($arr);
                case 'rch':
                    return RechargeBoundary::fromArray($arr);
                case 'wel':
                    return WellBoundary::fromArray($arr);
                default:
                    return null;
            }
        }

        if ($geoJson instanceof FeatureCollection) {
            foreach ($geoJson->getFeatures() as $feature) {
                Assertion::keyExists($feature->getProperties(), 'type');
                $type = $feature->getProperties()['type'];

                switch ($type) {
                    case 'chd':
                        return ConstantHeadBoundary::fromArray($arr);
                    case 'fhb':
                        return FlowAndHeadBoundary::fromArray($arr);
                    case 'drn':
                        return DrainageBoundary::fromArray($arr);
                    case 'ghb':
                        return GeneralHeadBoundary::fromArray($arr);
                    case 'riv':
                        return RiverBoundary::fromArray($arr);
                }
            }
        }

        return null;
    }
}
