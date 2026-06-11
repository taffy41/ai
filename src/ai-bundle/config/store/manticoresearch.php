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

return (new ArrayNodeDefinition('manticoresearch'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->end()
            ->scalarNode('table')->end()
            ->scalarNode('field')
                ->defaultValue('_vectors')
            ->end()
            ->scalarNode('type')
                ->defaultValue('hnsw')
            ->end()
            ->scalarNode('similarity')
                ->defaultValue('cosine')
            ->end()
            ->integerNode('dimensions')
                ->defaultValue(1536)
            ->end()
            ->scalarNode('quantization')->end()
        ->end()
    ->end();
