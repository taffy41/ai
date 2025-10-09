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

use Codewithkyrian\ChromaDB\Client as ChromaDbClient;
use MongoDB\Client as MongoDbClient;
use Probots\Pinecone\Client as PineconeClient;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory;
use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Store\Bridge\Postgres\Distance as PostgresDistance;
use Symfony\AI\Store\Bridge\Redis\Distance;
use Symfony\AI\Store\Document\VectorizerInterface;
use Symfony\AI\Store\StoreInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Contracts\Translation\TranslatorInterface;

return static function (DefinitionConfigurator $configurator): void {
    $configurator->rootNode()
        ->children()
            ->arrayNode('platform')
                ->children()
                    ->arrayNode('anthropic')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('version')->defaultNull()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('azure')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('api_key')->isRequired()->end()
                                ->scalarNode('base_url')->isRequired()->end()
                                ->scalarNode('deployment')->isRequired()->end()
                                ->scalarNode('api_version')->info('The used API version')->end()
                                ->scalarNode('http_client')
                                    ->defaultValue('http_client')
                                    ->info('Service ID of the HTTP client to use')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('eleven_labs')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('gemini')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('vertexai')
                        ->children()
                            ->scalarNode('location')->isRequired()->end()
                            ->scalarNode('project_id')->isRequired()->end()
                        ->end()
                    ->end()
                    ->arrayNode('openai')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('region')
                                ->defaultNull()
                                ->validate()
                                    ->ifNotInArray([null, PlatformFactory::REGION_EU, PlatformFactory::REGION_US])
                                    ->thenInvalid('The region must be either "EU" (https://eu.api.openai.com), "US" (https://us.api.openai.com) or null (https://api.openai.com)')
                                ->end()
                                ->info('The region for OpenAI API (EU, US, or null for default)')
                            ->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('mistral')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('openrouter')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('lmstudio')
                        ->children()
                            ->scalarNode('host_url')->defaultValue('http://127.0.0.1:1234')->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('ollama')
                        ->children()
                            ->scalarNode('host_url')->defaultValue('http://127.0.0.1:11434')->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cerebras')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('voyage')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('perplexity')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('dockermodelrunner')
                        ->children()
                            ->scalarNode('host_url')->defaultValue('http://127.0.0.1:12434')->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('scaleway')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('http_client')
                                ->defaultValue('http_client')
                                ->info('Service ID of the HTTP client to use')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('model')
                ->useAttributeAsKey('platform')
                ->arrayPrototype()
                    ->useAttributeAsKey('model_name')
                    ->normalizeKeys(false)
                    ->validate()
                        ->ifEmpty()
                        ->thenInvalid('Model name cannot be empty.')
                    ->end()
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('capabilities')
                                ->info('Array of capabilities that this model supports')
                                ->enumPrototype(Capability::class)
                                    ->values(array_map(static fn (Capability $a) => $a->value, Capability::cases()))
                                ->end()
                                ->defaultValue([])
                                ->validate()
                                    ->ifEmpty()
                                    ->thenInvalid('At least one capability must be specified for each model.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('agent')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('platform')
                            ->info('Service name of platform')
                            ->defaultValue(PlatformInterface::class)
                        ->end()
                        ->booleanNode('track_token_usage')
                            ->info('Enable tracking of token usage for the agent')
                            ->defaultTrue()
                        ->end()
                        ->variableNode('model')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !\is_string($v) && (!\is_array($v) || !isset($v['name']));
                                })
                                ->thenInvalid('Model must be a string or an array with a "name" key.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    // Check if both query parameters and options array are provided
                                    if (\is_array($v) && isset($v['name']) && isset($v['options']) && [] !== $v['options']) {
                                        return str_contains($v['name'], '?');
                                    }

                                    return false;
                                })
                                ->thenInvalid('Cannot use both query parameters in model name and options array.')
                            ->end()
                            ->beforeNormalization()
                                ->always(function ($v) {
                                    if (\is_string($v)) {
                                        return $v;
                                    }

                                    // It's an array with 'name' and optionally 'options'
                                    $model = $v['name'];
                                    $options = $v['options'] ?? [];

                                    // Parse query parameters from model name if present
                                    if (str_contains($model, '?')) {
                                        $parsed = parse_url($model);
                                        $model = $parsed['path'] ?? '';

                                        if ('' === $model) {
                                            throw new InvalidConfigurationException('Model name cannot be empty.');
                                        }

                                        if (isset($parsed['scheme'])) {
                                            $model = $parsed['scheme'].':'.$model;
                                        }

                                        if (isset($parsed['query'])) {
                                            // If options array is also provided, throw an error
                                            if ([] !== $options) {
                                                throw new InvalidConfigurationException('Cannot use both query parameters in model name and options array.');
                                            }
                                            parse_str($parsed['query'], $existingOptions);
                                            $options = $existingOptions;
                                        }
                                    }

                                    // Return model string with options as query parameters
                                    if ([] === $options) {
                                        return $model;
                                    }

                                    array_walk_recursive($options, static function (mixed &$value): void {
                                        if (\is_bool($value)) {
                                            $value = $value ? 'true' : 'false';
                                        }
                                    });

                                    return $model.'?'.http_build_query($options);
                                })
                            ->end()
                        ->end()
                        ->booleanNode('structured_output')->defaultTrue()->end()
                        ->variableNode('memory')
                            ->info('Memory configuration: string for static memory, or array with "service" key for service reference')
                            ->defaultNull()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_string($v) && '' === $v;
                                })
                                ->thenInvalid('Memory cannot be empty.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_array($v) && !isset($v['service']);
                                })
                                ->thenInvalid('Memory array configuration must contain a "service" key.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_array($v) && isset($v['service']) && '' === $v['service'];
                                })
                                ->thenInvalid('Memory service cannot be empty.')
                            ->end()
                        ->end()
                        ->arrayNode('prompt')
                            ->info('The system prompt configuration')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function (string $v) {
                                    return ['text' => $v];
                                })
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    if (!\is_array($v)) {
                                        return false;
                                    }
                                    $hasTextOrFile = isset($v['text']) || isset($v['file']);

                                    return !$hasTextOrFile;
                                })
                                ->thenInvalid('Either "text" or "file" must be configured for prompt.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_array($v) && isset($v['text']) && isset($v['file']);
                                })
                                ->thenInvalid('Cannot use both "text" and "file" for prompt. Choose one.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_array($v) && isset($v['text']) && '' === trim($v['text']);
                                })
                                ->thenInvalid('The "text" cannot be empty.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_array($v) && isset($v['file']) && '' === trim($v['file']);
                                })
                                ->thenInvalid('The "file" cannot be empty.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \is_array($v) && ($v['enabled'] ?? false) && !interface_exists(TranslatorInterface::class);
                                })
                                ->thenInvalid('System prompt translation is enabled, but no translator is present. Try running `composer require symfony/translation`.')
                            ->end()
                            ->children()
                                ->scalarNode('text')
                                    ->info('The system prompt text')
                                ->end()
                                ->scalarNode('file')
                                    ->info('Path to file containing the system prompt')
                                ->end()
                                ->booleanNode('include_tools')
                                    ->info('Include tool definitions at the end of the system prompt')
                                    ->defaultFalse()
                                ->end()
                                ->booleanNode('enable_translation')
                                    ->info('Enable translation for the system prompt')
                                    ->defaultFalse()
                                ->end()
                                ->scalarNode('translation_domain')
                                    ->info('The translation domain for the system prompt')
                                    ->defaultNull()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('tools')
                            ->addDefaultsIfNotSet()
                            ->treatFalseLike(['enabled' => false])
                            ->treatTrueLike(['enabled' => true])
                            ->treatNullLike(['enabled' => true])
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(function (array $v) {
                                    return [
                                        'enabled' => $v['enabled'] ?? true,
                                        'services' => $v['services'] ?? $v,
                                    ];
                                })
                            ->end()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->arrayNode('services')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('service')->cannotBeEmpty()->end()
                                            ->scalarNode('agent')->cannotBeEmpty()->end()
                                            ->scalarNode('name')->end()
                                            ->scalarNode('description')->end()
                                            ->scalarNode('method')->end()
                                        ->end()
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function (string $v) {
                                                return ['service' => $v];
                                            })
                                        ->end()
                                        ->validate()
                                            ->ifTrue(static fn ($v) => !(empty($v['agent']) xor empty($v['service'])))
                                            ->thenInvalid('Either "agent" or "service" must be configured, and never both.')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->booleanNode('fault_tolerant_toolbox')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('multi_agent')
                ->info('Multi-agent orchestration configuration')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('orchestrator')
                            ->info('Service ID of the orchestrator agent')
                            ->isRequired()
                        ->end()
                        ->arrayNode('handoffs')
                            ->info('Handoff rules mapping agent service IDs to trigger keywords')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('service')
                            ->arrayPrototype()
                                ->info('Keywords or phrases that trigger handoff to this agent')
                                ->requiresAtLeastOneElement()
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                        ->scalarNode('fallback')
                            ->info('Service ID of the fallback agent for unmatched requests')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('store')
                ->children()
                    ->arrayNode('azure_search')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->isRequired()->end()
                                ->scalarNode('api_key')->isRequired()->end()
                                ->scalarNode('index_name')->isRequired()->end()
                                ->scalarNode('api_version')->isRequired()->end()
                                ->scalarNode('vector_field')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cache')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('service')->cannotBeEmpty()->defaultValue('cache.app')->end()
                                ->scalarNode('cache_key')->end()
                                ->scalarNode('strategy')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('chroma_db')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('client')
                                    ->cannotBeEmpty()
                                    ->defaultValue(ChromaDbClient::class)
                                ->end()
                                ->scalarNode('collection')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('clickhouse')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('dsn')->cannotBeEmpty()->end()
                                ->scalarNode('http_client')->cannotBeEmpty()->end()
                                ->scalarNode('database')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('table')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v) => !isset($v['dsn']) && !isset($v['http_client']))
                                ->thenInvalid('Either "dsn" or "http_client" must be configured.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cloudflare')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('account_id')->cannotBeEmpty()->end()
                                ->scalarNode('api_key')->cannotBeEmpty()->end()
                                ->scalarNode('index_name')->cannotBeEmpty()->end()
                                ->integerNode('dimensions')->end()
                                ->scalarNode('metric')->end()
                                ->scalarNode('endpoint_url')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('meilisearch')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('api_key')->cannotBeEmpty()->end()
                                ->scalarNode('index_name')->cannotBeEmpty()->end()
                                ->scalarNode('embedder')->end()
                                ->scalarNode('vector_field')->end()
                                ->integerNode('dimensions')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('memory')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('strategy')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('milvus')
                    ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('api_key')->isRequired()->end()
                                ->scalarNode('database')->isRequired()->end()
                                ->scalarNode('collection')->isRequired()->end()
                                ->scalarNode('vector_field')->end()
                                ->integerNode('dimensions')->end()
                                ->scalarNode('metric_type')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('mongodb')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('client')
                                    ->cannotBeEmpty()
                                    ->defaultValue(MongoDbClient::class)
                                ->end()
                                ->scalarNode('database')->isRequired()->end()
                                ->scalarNode('collection')->isRequired()->end()
                                ->scalarNode('index_name')->isRequired()->end()
                                ->scalarNode('vector_field')->end()
                                ->booleanNode('bulk_write')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('neo4j')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('username')->cannotBeEmpty()->end()
                                ->scalarNode('password')->cannotBeEmpty()->end()
                                ->scalarNode('database')->cannotBeEmpty()->end()
                                ->scalarNode('vector_index_name')->cannotBeEmpty()->end()
                                ->scalarNode('node_name')->cannotBeEmpty()->end()
                                ->scalarNode('vector_field')->end()
                                ->integerNode('dimensions')->end()
                                ->scalarNode('distance')->end()
                                ->booleanNode('quantization')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('pinecone')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('client')
                                    ->cannotBeEmpty()
                                    ->defaultValue(PineconeClient::class)
                                ->end()
                                ->scalarNode('namespace')->end()
                                ->arrayNode('filter')
                                    ->scalarPrototype()->end()
                                ->end()
                                ->integerNode('top_k')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('qdrant')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('api_key')->cannotBeEmpty()->end()
                                ->scalarNode('collection_name')->cannotBeEmpty()->end()
                                ->integerNode('dimensions')->end()
                                ->scalarNode('distance')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('redis')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->variableNode('connection_parameters')
                                    ->info('see https://github.com/phpredis/phpredis?tab=readme-ov-file#example-1')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('client')
                                    ->info('a service id of a Redis client')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('index_name')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('key_prefix')->defaultValue('vector:')->end()
                                ->enumNode('distance')
                                    ->info('Distance metric to use for vector similarity search')
                                    ->values(Distance::cases())
                                    ->defaultValue(Distance::Cosine)
                                ->end()
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v) => !isset($v['connection_parameters']) && !isset($v['client']))
                                ->thenInvalid('Either "connection_parameters" or "client" must be configured.')
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v) => isset($v['connection_parameters']) && isset($v['client']))
                                ->thenInvalid('Either "connection_parameters" or "client" can be configured, but not both.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('surreal_db')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('username')->cannotBeEmpty()->end()
                                ->scalarNode('password')->cannotBeEmpty()->end()
                                ->scalarNode('namespace')->cannotBeEmpty()->end()
                                ->scalarNode('database')->cannotBeEmpty()->end()
                                ->scalarNode('table')->end()
                                ->scalarNode('vector_field')->end()
                                ->scalarNode('strategy')->end()
                                ->integerNode('dimensions')->end()
                                ->booleanNode('namespaced_user')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('typesense')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('api_key')->isRequired()->end()
                                ->scalarNode('collection')->isRequired()->end()
                                ->scalarNode('vector_field')->end()
                                ->integerNode('dimensions')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('weaviate')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('endpoint')->cannotBeEmpty()->end()
                                ->scalarNode('api_key')->isRequired()->end()
                                ->scalarNode('collection')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('message_store')
                ->children()
                    ->arrayNode('memory')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('identifier')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('cache')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('service')->cannotBeEmpty()->defaultValue('cache.app')->end()
                                ->scalarNode('key')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('session')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('identifier')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('vectorizer')
                ->info('Vectorizers for converting strings to Vector objects and transforming TextDocument arrays to VectorDocument arrays')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('platform')
                            ->info('Service name of platform')
                            ->defaultValue(PlatformInterface::class)
                        ->end()
                        ->variableNode('model')
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !\is_string($v) && (!\is_array($v) || !isset($v['name']));
                                })
                                ->thenInvalid('Model must be a string or an array with a "name" key.')
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    // Check if both query parameters and options array are provided
                                    if (\is_array($v) && isset($v['name']) && isset($v['options']) && [] !== $v['options']) {
                                        return str_contains($v['name'], '?');
                                    }

                                    return false;
                                })
                                ->thenInvalid('Cannot use both query parameters in model name and options array.')
                            ->end()
                            ->beforeNormalization()
                                ->always(function ($v) {
                                    if (\is_string($v)) {
                                        return $v;
                                    }

                                    // It's an array with 'name' and optionally 'options'
                                    $model = $v['name'];
                                    $options = $v['options'] ?? [];

                                    // Parse query parameters from model name if present
                                    if (str_contains($model, '?')) {
                                        $parsed = parse_url($model);
                                        $model = $parsed['path'] ?? '';

                                        if ('' === $model) {
                                            throw new InvalidConfigurationException('Model name cannot be empty.');
                                        }

                                        if (isset($parsed['scheme'])) {
                                            $model = $parsed['scheme'].':'.$model;
                                        }

                                        if (isset($parsed['query'])) {
                                            // If options array is also provided, throw an error
                                            if ([] !== $options) {
                                                throw new InvalidConfigurationException('Cannot use both query parameters in model name and options array.');
                                            }
                                            parse_str($parsed['query'], $existingOptions);
                                            $options = $existingOptions;
                                        }
                                    }

                                    // Return model string with options as query parameters
                                    if ([] === $options) {
                                        return $model;
                                    }

                                    array_walk_recursive($options, static function (mixed &$value): void {
                                        if (\is_bool($value)) {
                                            $value = $value ? 'true' : 'false';
                                        }
                                    });

                                    return $model.'?'.http_build_query($options);
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('indexer')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('loader')
                            ->info('Service name of loader')
                            ->isRequired()
                        ->end()
                        ->variableNode('source')
                            ->info('Source identifier (file path, URL, etc.) or array of sources')
                            ->defaultNull()
                        ->end()
                        ->arrayNode('transformers')
                            ->info('Array of transformer service names')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('filters')
                            ->info('Array of filter service names')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->scalarNode('vectorizer')
                            ->info('Service name of vectorizer')
                            ->defaultValue(VectorizerInterface::class)
                        ->end()
                        ->scalarNode('store')
                            ->info('Service name of store')
                            ->defaultValue(StoreInterface::class)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ->validate()
            ->ifTrue(function ($v) {
                if (!isset($v['agent']) || !isset($v['multi_agent'])) {
                    return false;
                }

                $agentNames = array_keys($v['agent']);
                $multiAgentNames = array_keys($v['multi_agent']);
                $duplicates = array_intersect($agentNames, $multiAgentNames);

                return !empty($duplicates);
            })
            ->then(function ($v) {
                $agentNames = array_keys($v['agent'] ?? []);
                $multiAgentNames = array_keys($v['multi_agent'] ?? []);
                $duplicates = array_intersect($agentNames, $multiAgentNames);

                throw new \InvalidArgumentException(\sprintf('Agent names and multi-agent names must be unique. Duplicate name(s) found: "%s"', implode(', ', $duplicates)));
            })
        ->end()
        ->validate()
            ->ifTrue(function ($v) {
                if (!isset($v['multi_agent']) || !isset($v['agent'])) {
                    return false;
                }

                $agentNames = array_keys($v['agent']);

                foreach ($v['multi_agent'] as $multiAgentName => $multiAgent) {
                    // Check orchestrator exists
                    if (!\in_array($multiAgent['orchestrator'], $agentNames, true)) {
                        return true;
                    }

                    // Check fallback exists
                    if (!\in_array($multiAgent['fallback'], $agentNames, true)) {
                        return true;
                    }

                    // Check handoff agents exist
                    foreach (array_keys($multiAgent['handoffs']) as $handoffAgent) {
                        if (!\in_array($handoffAgent, $agentNames, true)) {
                            return true;
                        }
                    }
                }

                return false;
            })
            ->then(function ($v) {
                $agentNames = array_keys($v['agent']);

                foreach ($v['multi_agent'] as $multiAgentName => $multiAgent) {
                    if (!\in_array($multiAgent['orchestrator'], $agentNames, true)) {
                        throw new \InvalidArgumentException(\sprintf('The agent "%s" referenced in multi-agent "%s" as orchestrator does not exist', $multiAgent['orchestrator'], $multiAgentName));
                    }

                    if (!\in_array($multiAgent['fallback'], $agentNames, true)) {
                        throw new \InvalidArgumentException(\sprintf('The agent "%s" referenced in multi-agent "%s" as fallback does not exist', $multiAgent['fallback'], $multiAgentName));
                    }

                    foreach (array_keys($multiAgent['handoffs']) as $handoffAgent) {
                        if (!\in_array($handoffAgent, $agentNames, true)) {
                            throw new \InvalidArgumentException(\sprintf('The agent "%s" referenced in multi-agent "%s" as handoff target does not exist', $handoffAgent, $multiAgentName));
                        }
                    }
                }
            })
        ->end()
    ;
};
