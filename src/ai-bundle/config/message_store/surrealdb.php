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
            ->booleanNode('namespaced_user')
                ->info('Using a namespaced user is a good practice to prevent any undesired access to a specific table, see https://surrealdb.com/docs/surrealdb/reference-guide/security-best-practices')
            ->end()
        ->end()
    ->end();
