<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;
use Tequila\MongoDB\ServerInfo;

class CreateCollection implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     * @param array $options
     */
    public function __construct($collectionName, array $options = [])
    {
        $this->options = ['create' => (string)$collectionName] + self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->options;
    }

    /**
     * @param  OptionsResolver $resolver
     */
    private static function configureOptions(OptionsResolver $resolver)
    {
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined([
            'capped',
            'size',
            'max',
            'flags',
            'storageEngine',
            'validator',
            'validationLevel',
            'validationAction',
            'indexOptionDefaults',
        ]);

        $resolver
            ->setAllowedTypes('capped', 'bool')
            ->setAllowedTypes('size', 'integer')
            ->setAllowedTypes('max', 'integer')
            ->setAllowedTypes('flags', 'integer')
            ->setAllowedTypes('storageEngine', ['array', 'object'])
            ->setAllowedTypes('validator', ['array', 'object'])
            ->setAllowedValues('validationLevel', [
                'off',
                'strict',
                'moderate',
            ])
            ->setAllowedValues('validationAction', [
                'error',
                'warn',
            ])
            ->setAllowedTypes('indexOptionDefaults', ['array', 'object']);

        $resolver->setDefault('size', function(Options $options) {
            if (isset($options['capped']) && true === $options['capped']) {
                throw new InvalidArgumentException(
                    'The option "size" is required for capped collections'
                );
            }

            return 0;
        });

        $sizeAndMaxOptionsNormalizer = function(Options $options, $value) {
            if ($value && isset($options['capped']) && false === $options['capped']) {
                throw new InvalidArgumentException(
                    'The "size" and "max" options are meaningless until "capped" option has been set to true'
                );
            }

            return $value;
        };

        $resolver->setNormalizer('size', $sizeAndMaxOptionsNormalizer);
        $resolver->setNormalizer('max', $sizeAndMaxOptionsNormalizer);
    }
}