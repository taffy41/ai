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

return (new ArrayNodeDefinition('neo4j'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->end()
            ->scalarNode('username')->cannotBeEmpty()->end()
            ->scalarNode('password')->cannotBeEmpty()->end()
            ->scalarNode('database')->end()
            ->scalarNode('vector_index_name')->cannotBeEmpty()->end()
            ->scalarNode('node_name')->cannotBeEmpty()->end()
            ->scalarNode('vector_field')
                ->defaultValue('embeddings')
            ->end()
            ->integerNode('dimensions')
                ->defaultValue(1536)
            ->end()
            ->scalarNode('distance')
                ->defaultValue('cosine')
            ->end()
            ->booleanNode('quantization')->end()
        ->end()
    ->end();
