<?php

namespace Wizhippo\eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter as BaseConverter;
use eZ\Publish\Core\Persistence\TransformationProcessor;

/**
 * URL slug converter.
 *
 * class SlugConverterPass implements CompilerPassInterface
 * {
 *      public function process(ContainerBuilder $container)
 *      {
 *          if (!$container->has('ezpublish.persistence.slug_converter')) {
 *          return;
 *      }
 *
 *      $definition = $container->findDefinition('ezpublish.persistence.slug_converter');
 *      $definition->setClass('TravelOnly\eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter');
 *      }
 * }
 *
 */
class SlugConverter extends BaseConverter
{
    /**
     * Creates a new URL slug converter.
     *
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     * @param array $configuration
     */
    public function __construct(TransformationProcessor $transformationProcessor, array $configuration = [])
    {
        $this->configuration['transformation'] = 'urlalias_lower';
        $this->configuration['transformationGroups']['urlalias_lower'] = [
            'commands' => [
                //normalize
                'space_normalize',
                'hash_normalize',
                'apostrophe_normalize',
                'doublequote_normalize',
                'greek_normalize',
                'endline_search_normalize',
                'tab_search_normalize',
                'specialwords_search_normalize',

                //transform
                'apostrophe_to_doublequote',
                'math_to_ascii',
                'inverted_to_normal',

                //decompose
                'special_decompose',
                'latin_search_decompose',

                //transliterate
                'cyrillic_transliterate_ascii',
                'greek_transliterate_ascii',
                'hebrew_transliterate_ascii',
                'latin1_transliterate_ascii',
                'latin-exta_transliterate_ascii',

                //diacritical
                'cyrillic_diacritical',
                'greek_diacritical',
                'latin1_diacritical',
                'latin-exta_diacritical',

                //lowercase
                'ascii_lowercase',
                'cyrillic_lowercase',
                'greek_lowercase',
                'latin1_lowercase',
                'latin-exta_lowercase',
                'latin_lowercase',
            ],
            'cleanupMethod' => 'url_cleanup',
        ];

        parent::__construct($transformationProcessor, $configuration);
    }
}
