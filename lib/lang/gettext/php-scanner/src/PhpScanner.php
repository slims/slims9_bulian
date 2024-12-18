<?php
declare(strict_types = 1);

namespace Gettext\Scanner;

use Gettext\Translation;
use Gettext\Translations;

/**
 * Class to scan PHP files and get gettext translations
 */
class PhpScanner extends CodeScanner
{
    use FunctionsHandlersTrait;

    protected $functions = [
        'gettext' => 'gettext',
        '_' => 'gettext',
        '__' => 'gettext',
        'ngettext' => 'ngettext',
        'n__' => 'ngettext',
        'pgettext' => 'pgettext',
        'p__' => 'pgettext',
        'dgettext' => 'dgettext',
        'd__' => 'dgettext',
        'dngettext' => 'dngettext',
        'dn__' => 'dngettext',
        'dpgettext' => 'dpgettext',
        'dp__' => 'dpgettext',
        'npgettext' => 'npgettext',
        'np__' => 'npgettext',
        'dnpgettext' => 'dnpgettext',
        'dnp__' => 'dnpgettext',
        'noop' => 'gettext',
        'noop__' => 'gettext',
    ];

    public function getFunctionsScanner(): FunctionsScannerInterface
    {
        return new PhpFunctionsScanner(array_keys($this->functions));
    }

    protected function saveTranslation(
        ?string $domain,
        ?string $context,
        string $original,
        string $plural = null
    ): ?Translation {
        $translation = parent::saveTranslation($domain, $context, $original, $plural);

        if (!$translation) {
            return null;
        }

        $original = $translation->getOriginal();

        //Check if it includes a sprintf
        if (strpos($original, '%') !== false) {
            // %[argnum$][flags][width][.precision]specifier
            if (preg_match('/%(\d+\$)?([\-\+\s0]|\'.)?(\d+)?(\.\d+)?[bcdeEfFgGhHosuxX]/', $original)) {
                $translation->getFlags()->add('php-format');
            }
        }

        return $translation;
    }
}
