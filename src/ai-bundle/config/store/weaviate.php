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

return (new ArrayNodeDefinition('weaviate'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->end()
            ->scalarNode('api_key')->end()
            ->scalarNode('http_client')
                ->defaultValue('http_client')
            ->end()
            ->scalarNode('collection')
                ->info('The name of the store will be used if the "collection" is not set')
            ->end()
        ->end()
    ->end();
