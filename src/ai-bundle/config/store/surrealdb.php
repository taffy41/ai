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

return (new ArrayNodeDefinition('surrealdb'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->end()
            ->scalarNode('username')->cannotBeEmpty()->end()
            ->scalarNode('password')->cannotBeEmpty()->end()
            ->scalarNode('namespace')->cannotBeEmpty()->end()
            ->scalarNode('database')->cannotBeEmpty()->end()
            ->scalarNode('table')->end()
            ->scalarNode('http_client')
                ->defaultValue('http_client')
            ->end()
            ->scalarNode('vector_field')
                ->defaultValue('_vectors')
            ->end()
            ->scalarNode('strategy')
                ->defaultValue('cosine')
            ->end()
            ->integerNode('dimensions')
                ->defaultValue(1536)
            ->end()
            ->booleanNode('namespaced_user')->end()
        ->end()
    ->end();
