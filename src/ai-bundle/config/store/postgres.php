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

return (new ArrayNodeDefinition('postgres'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('dsn')->cannotBeEmpty()->end()
            ->scalarNode('username')->end()
            ->scalarNode('password')->end()
            ->scalarNode('table_name')->end()
            ->scalarNode('vector_field')
                ->defaultValue('embedding')
            ->end()
            ->enumNode('distance')
                ->info('Distance metric to use for vector similarity search')
                ->values(['cosine', 'inner_product', 'l1', 'l2'])
                ->defaultValue('l2')
            ->end()
            ->scalarNode('lang')
                ->defaultValue('english')
            ->end()
            ->scalarNode('dbal_connection')->cannotBeEmpty()->end()
            ->arrayNode('setup_options')
                ->children()
                    ->scalarNode('vector_type')->defaultValue('vector')->end()
                    ->integerNode('vector_size')->defaultValue(1536)->end()
                    ->scalarNode('index_method')->defaultValue('ivfflat')->end()
                    ->scalarNode('index_opclass')->defaultValue('vector_cosine_ops')->end()
                ->end()
            ->end()
        ->end()
        ->validate()
            ->ifTrue(static fn (array $v): bool => !isset($v['dsn']) && !isset($v['dbal_connection']))
            ->thenInvalid('Either "dsn" or "dbal_connection" must be configured.')
        ->end()
        ->validate()
            ->ifTrue(static fn (array $v): bool => isset($v['dsn'], $v['dbal_connection']))
            ->thenInvalid('Either "dsn" or "dbal_connection" can be configured, but not both.')
        ->end()
    ->end();
