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

return (new ArrayNodeDefinition('opensearch'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->end()
            ->scalarNode('index_name')->end()
            ->scalarNode('vectors_field')
                ->defaultValue('_vectors')
            ->end()
            ->integerNode('dimensions')
                ->defaultValue(1536)
            ->end()
            ->scalarNode('space_type')
                ->defaultValue('l2')
            ->end()
            ->scalarNode('http_client')
                ->defaultValue('http_client')
            ->end()
        ->end()
    ->end();
