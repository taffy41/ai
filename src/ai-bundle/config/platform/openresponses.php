<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Definition\Configurator;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

return (new ArrayNodeDefinition('openresponses'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('base_url')->isRequired()->end()
            ->scalarNode('api_key')->end()
            ->scalarNode('http_client')
                ->defaultValue('http_client')
                ->info('Service ID of the HTTP client to use')
            ->end()
            ->scalarNode('model_catalog')
                ->info('Service ID of the model catalog to use')
            ->end()
            ->scalarNode('responses_path')->defaultValue('/v1/responses')->end()
        ->end()
    ->end();
