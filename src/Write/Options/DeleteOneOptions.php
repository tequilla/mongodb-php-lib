<?php

namespace Tequila\MongoDB\Write\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\ConfigurableInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DeleteOneOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        DeleteOptions::configureOptions($resolver);
        $resolver->setDefault('limit', 1);
        $resolver->setNormalizer('limit', function (Options $options, $limit) {
            if (0 === $limit) {
                throw new InvalidArgumentException(
                    'Option "limit" cannot be set to 0 for DeleteOne operation. If you want to delete multiple documents - use DeleteMany'
                );
            }

            return $limit;
        });
    }
}