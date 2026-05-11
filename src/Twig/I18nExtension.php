<?php

namespace SimpleMappr\Twig;

use Twig\Extension\AbstractExtension;

/**
 * Replacement for the abandoned twig/extensions I18n extension.
 *
 * Registers a {% trans %} token parser that compiles to PHP's gettext()
 * (or _() / dgettext) so the existing locale catalogues under i18n/ keep
 * working under Twig 3 without pulling in symfony/translation.
 */
class I18nExtension extends AbstractExtension
{
    public function getTokenParsers(): array
    {
        return [new TransTokenParser()];
    }

    public function getName(): string
    {
        return 'i18n';
    }
}
