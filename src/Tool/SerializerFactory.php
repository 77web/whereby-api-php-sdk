<?php

declare(strict_types=1);

namespace Nanaweb\WherebyApi\Tool;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @description for non-symfony usage.
 */
class SerializerFactory
{
    public function create(): SerializerInterface
    {
        return new Serializer(
            [
                new DateTimeNormalizer(),
                new BackedEnumNormalizer(),
                new JsonSerializableNormalizer(),
                new ObjectNormalizer(propertyTypeExtractor: new ReflectionExtractor()),
            ],
            [
                new JsonEncoder(),
            ],
        );
    }
}