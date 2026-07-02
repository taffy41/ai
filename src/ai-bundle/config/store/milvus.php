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

return (new ArrayNodeDefinition('milvus'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->end()
            ->scalarNode('api_key')->isRequired()->end()
            ->scalarNode('database')->end()
            ->scalarNode('collection')->isRequired()->end()
            ->scalarNode('vector_field')
                ->defaultValue('_vectors')
            ->end()
            ->integerNode('dimensions')
                ->defaultValue(1536)
            ->end()
            ->scalarNode('metric_type')
                ->defaultValue('COSINE')
            ->end()
        ->end()
    ->end();
