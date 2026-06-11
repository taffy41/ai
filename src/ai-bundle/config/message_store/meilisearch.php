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

return (new ArrayNodeDefinition('meilisearch'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->end()
            ->scalarNode('api_key')->cannotBeEmpty()->end()
            ->scalarNode('index_name')->cannotBeEmpty()->end()
        ->end()
    ->end();
