<?php

use PHPUnit\Framework\TestCase;
use Salamander\ArrayMapper\ArrayMapper;

class ArrayMapperTest extends TestCase
{
    protected $sampleResponse;

    /** @var Salamander\ArrayMapper\ArrayMapper */
    protected $mapper;

    public function testMapSimple()
    {
        $mapping = [
            'my_id' => 'id',
            'my_type' => 'type',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'my_type' => $this->sampleResponse->type,
            ],
            $mappedData
        );
    }

    public function testMapInvalidSimpleInvalid()
    {
        $mapping = [
            'my_id' => 'id',
            'my_type' => 'type_does_not_exist',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
            ],
            $mappedData
        );
    }

    public function testMapNestedSimple()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'my_type' => 'type',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'nested' => [
                    'my_type' => $this->sampleResponse->type,
                ],
            ],
            $mappedData
        );
    }

    public function testMapNestedSimpleOnlyInvalid()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'my_type' => 'type_does_not_exist',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
            ],
            $mappedData
        );
    }

    public function testMapNestedSimpleInvalid()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'my_wrong_type' => 'type_does_not_exist',
                'my_type' => 'type',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'nested' => [
                    'my_type' => $this->sampleResponse->type,
                ],
            ],
            $mappedData
        );
    }

    public function testMapProperty()
    {
        $mapping = [
            'my_id' => 'id',
            'description' => 'description.nl',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'description' => $this->sampleResponse->description->nl,
            ],
            $mappedData
        );
    }

    public function testMapPropertyInvalid()
    {
        $mapping = [
            'my_id' => 'id',
            'description' => 'description.nl_does_not_exist',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
            ],
            $mappedData
        );
    }

    public function testMapPropertyNested()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'description' => 'description.nl',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'nested' => [
                    'description' => $this->sampleResponse->description->nl,
                ],
            ],
            $mappedData
        );
    }

    public function testMapPropertyNestedOnlyInvalid()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'description' => 'description.nl_does_not_exist',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
            ],
            $mappedData
        );
    }

    public function testMapPropertyNestedInvalid()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'description_invalid' => 'description.nl_does_not_exist',
                'description' => 'description.nl',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'nested' => [
                    'description' => $this->sampleResponse->description->nl,
                ],
            ],
            $mappedData
        );
    }

    public function testMapPropertyArray()
    {
        $mapping = [
            'my_id' => 'id',
            'plan_ids' => 'plans.*.id',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $expectedIds = [];
        foreach ($this->sampleResponse->plans as $plan) {
            $expectedIds[] = $plan->id;
        }

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'plan_ids' => $expectedIds,
            ],
            $mappedData
        );
    }

    public function testMapPropertyNestedArray()
    {
        $mapping = [
            'my_id' => 'id',
            'test_array' => 'nested.*.double_nested.*.test',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $expectedArray = [];
        foreach ($this->sampleResponse->nested as $nest) {
            foreach ($nest->double_nested as $doubleNest) {
                $expectedArray[] = $doubleNest->test;
            }
        }

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'test_array' => $expectedArray,
            ],
            $mappedData
        );
    }

    public function testMapArray()
    {
        $mapping = [
            'my_id' => 'id',
            'test_array.*' => [
                'id' => 'nested.*.id',
                'name' => 'nested.*.title',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $expectedArray = [];
        foreach ($this->sampleResponse->nested as $nest) {
            $expectedArray[] = ['id' => $nest->id, 'name' => $nest->title];
        }

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'test_array' => $expectedArray,
            ],
            $mappedData
        );
    }

    public function testMapArrayNestedArray()
    {
        $mapping = [
            'my_id' => 'id',
            'test_array.*' => [
                'id' => 'nested.*.id',
                'name' => 'nested.*.title',
                'double_nested' => 'nested.*.double_nested.*.test',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $expectedArray = [];
        foreach ($this->sampleResponse->nested as $nest) {
            $child = [
                'id' => $nest->id,
                'name' => $nest->title,
            ];
            foreach ($nest->double_nested as $doubleNest) {
                $child['double_nested'][] = $doubleNest->test;
            }
            $expectedArray[] = $child;
        }

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'test_array' => $expectedArray,
            ],
            $mappedData
        );
    }

    public function testMapArrayNested()
    {
        $mapping = [
            'my_id' => 'id',
            'test_array.*' => [
                'id' => 'nested.*.id',
                'nested.*' => [
                    'name' => 'nested.*.title',
                ],
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $expectedArray = [];
        foreach ($this->sampleResponse->nested as $nest) {
            $expectedArray[] = [
                'id' => $nest->id,
                'nested' => ['name' => $nest->title],
            ];
        }

        $this->assertEquals(
            [
                'my_id' => $this->sampleResponse->id,
                'test_array' => $expectedArray,
            ],
            $mappedData
        );
    }

    public function testMapArrayMerged()
    {
        $mapping = [
            'merged.*' => [
                [
                    'id' => 'documents.*.id',
                    'description' => 'documents.*.description',
                ],
                [
                    'id' => 'images.*.id',
                    'description' => 'images.*.description',
                ],
            ],
        ];

        $expectedArray = [
            'merged' => [],
        ];
        foreach ($this->sampleResponse->documents as $document) {
            $expectedArray['merged'][] = ['id' => $document->id, 'description' => $document->description];
        }
        foreach ($this->sampleResponse->images as $image) {
            $expectedArray['merged'][] = ['id' => $image->id, 'description' => $image->description];
        }

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expectedArray, $mappedData);
    }

    public function testMapFixedValue()
    {
        $mapping = [
            'my_id' => 'id',
            'fixed_value' => '#fixed value',
        ];

        $expectedArray = [
            'my_id' => $this->sampleResponse->id,
            'fixed_value' => 'fixed value',
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expectedArray, $mappedData);
    }

    public function testMapFixedValueEscape()
    {
        $mapping = [
            'my_id' => 'id',
            'escaped' => '##prefixed',
        ];

        $expectedArray = [
            'my_id' => $this->sampleResponse->id,
            'escaped' => $this->sampleResponse->{'#prefixed'},
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expectedArray, $mappedData);
    }

    public function testMapFixedValueNested()
    {
        $mapping = [
            'my_id' => 'id',
            'nested' => [
                'fixed_value' => '#fixed value',
            ],
        ];

        $expectedArray = [
            'my_id' => $this->sampleResponse->id,
            'nested' => [
                'fixed_value' => 'fixed value',
            ],
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expectedArray, $mappedData);
    }

    public function testMapFixedValueArray()
    {
        $mapping = [
            'my_id' => 'id',
            'test_array.*' => [
                'id' => 'nested.*.id',
                'name' => 'nested.*.title',
                'fixed_value' => '#fixed value',
            ],
        ];

        $expectedArray = [];
        foreach ($this->sampleResponse->nested as $nest) {
            $expectedArray[] = ['id' => $nest->id, 'name' => $nest->title, 'fixed_value' => 'fixed value'];
        }

        $expected = [
            'my_id' => $this->sampleResponse->id,
            'test_array' => $expectedArray,
        ];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expected, $mappedData);
    }

    public function testMapArraySingleValue()
    {
        $mapping = [
            'test_array.*' => [
                'id' => 'documents.*.id',
                'description' => 'documents.*.description',
                'gas' => 'features.energy.gas',
            ],
        ];

        $expectedArray = [
            'test_array' => [],
        ];
        foreach ($this->sampleResponse->documents as $document) {
            $expectedArray['test_array'][] = ['id' => $document->id, 'description' => $document->description, 'gas' => $this->sampleResponse->features->energy->gas];
        }

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expectedArray, $mappedData);
    }

    public function testMapArrayMergedSingleValue()
    {
        $mapping = [
            'merged.*' => [
                [
                    'id' => 'documents.*.id',
                    'description' => 'documents.*.description',
                ],
                ['gas' => 'features.energy.gas'],
                ['fireplace' => 'features.comfort.fireplace'],
            ],
        ];

        $expectedArray = [
            'merged' => [],
        ];
        foreach ($this->sampleResponse->documents as $document) {
            $expectedArray['merged'][] = ['id' => $document->id, 'description' => $document->description];
        }
        $expectedArray['merged'][] = ['gas' => $this->sampleResponse->features->energy->gas];
        $expectedArray['merged'][] = ['fireplace' => $this->sampleResponse->features->comfort->fireplace];

        $mappedData = $this->mapper->map($this->sampleResponse, $mapping);

        $this->assertEquals($expectedArray, $mappedData);
    }

    protected function setUp(): void
    {
        $this->mapper = new ArrayMapper();
        $this->sampleResponse = json_decode('{
              "id": "69cbfdd6-b3e4-470e-a672-5c9e37fe36e9",
              "is_project": true,
              "project_id": "32ed6c2a-716f-4077-9779-2b7794e46257",
              "type": "apartment",
              "sub_type": "condo",
              "negotiation": "let",
              "rent_period": "month",
              "status": "available",
              "description": {
                "en": "Apartment with pool",
                "fr": "Appartement avec piscine",
                "nl": "Appartement met zwembad"
              },
              "description_title": {
                "en": "Luxury Apartment",
                "fr": "Appartement de luxe",
                "nl": "Luxe appartement"
              },
              "living_rooms": 1,
              "kitchens": 1,
              "bedrooms": 1,
              "bathrooms": 1,
              "toilets": 1,
              "floors": 1,
              "showrooms": 1,
              "manufacturing_areas": 1,
              "storage_rooms": 1,
              "kitchen_condition": "good",
              "bathroom_condition": "good",
              "garden_orientation": "N",
              "terrace_orientation": "N",
              "video_url": "http://my-video-url.be",
              "appointment_service_url": "http://my-calendar-url.com",
              "general_condition": "poor",
              "legal": {
                "energy": {
                  "epc_value": 200,
                  "epc_category": "A++",
                  "epc_reference": "20081101-0000000245-00000015",
                  "total_epc_value": 1000,
                  "nabers": {
                    "description": "Historical and current water and energy use",
                    "type": "number",
                    "maximum": 6,
                    "minimum": 0
                  },
                  "nathers": {
                    "description": "All features of a building, including location",
                    "type": "number",
                    "maximum": 10,
                    "minimum": 0
                  },
                  "co2_emissions": "",
                  "e_level": "E90",
                  "report_electricity_gas": "conform",
                  "report_fuel_tank": "conform"
                },
                "regulations": {
                  "building_permit": true,
                  "priority_purchase_right": true,
                  "subdivision_authorisation": true,
                  "urban_planning_breach": true,
                  "as_built_report": true,
                  "expropriation_plan": true,
                  "heritage_list": true,
                  "pending_legal_proceedings": true,
                  "registered_building": true,
                  "site_untapped_activity": true,
                  "urban_planning_certificate": true
                },
                "legal_mentions": {
                  "en": "Legal regulations English",
                  "fr": "Legal regulations French",
                  "nl": "Legal regulations Dutch"
                },
                "property_and_land": {
                  "purchased_year": "1987",
                  "cadastral_income": 785,
                  "flood_risk": "no_flood_risk_area",
                  "land_use_designation": "residential"
                }
              },
              "auction": {
                "start_date": "2018-01-01T10:00:00+00:00"
              },
              "open_homes": [
                {
                  "start_date": "2018-05-26T10:00:00+00:00",
                  "end_date": "2018-05-26T10:00:00+00:00"
                }
              ],
              "price": {
                "amount": 100000,
                "currency": "EUR",
                "hidden": true
              },
              "price_negotiated": 100000,
              "price_costs": {
                "en": "Free text field English",
                "fr": "Free text field French",
                "nl": "Free text field Dutch"
              },
              "price_taxes": {
                "en": "Free text field English",
                "fr": "Free text field French",
                "nl": "Free text field Dutch"
              },
              "custom_price": "Price available on request",
              "location": {
                "geo": {
                  "latitude": 1.2345,
                  "longitude": 1.2345
                },
                "city": "Brussels",
                "street": "Street name",
                "street_2": "Street name",
                "number": "123",
                "box": "7",
                "addition": "ABD",
                "country": "BE",
                "formatted": "Street name, Brussels",
                "formatted_agency": "Formatted Agency",
                "postal_code": "1000",
                "hidden": false
              },
              "amenities": [
                "pool"
              ],
              "sizes": {
                "plot_area": {
                  "size": 100,
                  "unit": "sq_ft"
                },
                "liveable_area": {
                  "size": 100,
                  "unit": "sq_ft"
                }
              },
              "permissions": {
                "farming": true,
                "fishing": false,
                "planning": false,
                "construction": true
              },
              "rooms": [
                {
                  "type": "living_room",
                  "size_description": "Living room",
                  "size": 100,
                  "unit": "sq_ft",
                  "ordinal": 1
                }
              ],
              "images": [
                {
                  "id": "1234-5678-9012",
                  "filename": "my-image.jpeg",
                  "description": "My image",
                  "url": "http://absolute-url-to-download-image-from/image.jpg",
                  "url_expires_on": "2017-01-01T12:12:12+00:00",
                  "ordinal": 1
                }
              ],
              "plans": [
                {
                  "id": "1234-5678-9012",
                  "filename": "my-plan.pdf",
                  "description": "My plan",
                  "url": "http://absolute-url-to-download-plan-from/plan.pdf",
                  "url_expires_on": "2017-01-01T12:12:12+00:00"
                }
              ],
              "documents": [
                {
                  "id": "1234-5678-9012",
                  "filename": "my-plan.pdf",
                  "description": "My plan",
                  "content_type": "application/pdf",
                  "url": "http://absolute-url-to-download-plan-from/plan.pdf",
                  "url_expires_on": "2017-01-01T12:12:12+00:00"
                },
                {
                  "id": "1234-5678-90122",
                  "filename": "my-plan.pdf",
                  "description": "My plan2",
                  "content_type": "application/pdf",
                  "url": "http://absolute-url-to-download-plan-from/plan.pdf",
                  "url_expires_on": "2017-01-01T12:12:12+00:00"
                }
              ],
              "features": {
                "energy": {
                  "gas": true,
                  "fuel": true,
                  "electricity": true,
                  "heat_pump": true
                },
                "comfort": {
                  "home_automation": true,
                  "water_softener": true,
                  "fireplace": true,
                  "walk_in_closet": true,
                  "home_cinema": true,
                  "wine_cellar": true,
                  "sauna": true,
                  "fitness_room": true,
                  "furnished": true
                },
                "ecology": {
                  "double_glazing": true,
                  "solar_panels": true,
                  "solar_boiler": true,
                  "rainwater_harvesting": true,
                  "insulated_roof": true
                },
                "security": {
                  "alarm": true,
                  "concierge": true,
                  "video_surveillance": true
                },
                "heating_cooling": {
                  "central_heating": true,
                  "floor_heating": true,
                  "air_conditioning": true
                }
              },
              "building": {
                "renovation": {
                  "year": 2012,
                  "description": "New windows"
                },
                "construction": {
                  "year": 1970,
                  "architect": "Mr. Architect"
                }
              },
              "negotiator": {
                "first_name": "John",
                "last_name": "Doe",
                "email": "john@doe",
                "phone": "+123456789",
                "photo_url": "http://absolute-url-to-download-photo-from/photo.jpg",
                "photo_url_expires_on": "2017-01-01T12:12:12+00:00"
              },
              "agency_commission": {
                "fixed_fee": 500.2,
                "percentage": 5
              },
              "mandate": {
                "start_date": "2018-01-01",
                "end_date": "2019-01-01",
                "exclusive": true
              },
              "internal_note": "Internal note",
              "occupancy": {
                "occupied": false,
                "available_from": "2018-01-01",
                "contact_details": "Available next week",
                "tenant_contract": {
                  "end_date": "2018-12-31",
                  "start_date": "2018-01-01"
                }
              },
              "office": {
                "id": "2e8caade-eb1e-4499-994f-4aed28906826",
                "name": "Team 1"
              },
              "buyers": [
                {
                  "first_name": "John",
                  "last_name": "Doe",
                  "email": "john@doe.com",
                  "phone": "+123456789"
                }
              ],
              "vendors": [
                {
                  "first_name": "John",
                  "last_name": "Doe",
                  "email": "john@doe.com",
                  "phone": "+123456789"
                }
              ],
              "settings": {
                "reference": "internal reference"
              },
              "properties": [
                {}
              ],
              "nested": [
                {
                    "id": "1",
                    "title": "naam 1",
                    "description": "desc",
                    "double_nested": [
                        {
                            "test": "a"
                        },
                        {
                            "test": "b"
                        }
                    ]
                },
                {
                    "id": "2",
                    "title": "naam 2",
                    "double_nested": [
                        {
                            "test": "c"
                        },
                        {
                            "test": "d"
                        }
                    ]
                }
            ],
            "#prefixed": "prefixed value"
            }');
    }
}
