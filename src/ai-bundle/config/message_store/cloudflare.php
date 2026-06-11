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

return (new ArrayNodeDefinition('cloudflare'))
    ->useAttributeAsKey('name')
    ->arrayPrototype()
        ->children()
            ->scalarNode('account_id')->cannotBeEmpty()->end()
            ->scalarNode('api_key')->cannotBeEmpty()->end()
            ->scalarNode('namespace')->cannotBeEmpty()->end()
            ->scalarNode('endpoint_url')
                ->info('If the version of the Cloudflare API is updated, use this key to support it.')
            ->end()
        ->end()
    ->end();
