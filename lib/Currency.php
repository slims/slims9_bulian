<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-10 23:32:04
 * @modify date 2022-10-11 01:20:17
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

class Currency
{
    private mixed $input;
    private ?object $formatter = null;

    public function __construct(mixed $input = null)
    {
        $this->input = $input;
        if ($this->isSupport()) $this->formatter = new \NumberFormatter(config('custom_currency_locale', config('default_lang')), \NumberFormatter::CURRENCY);
    }

    /**
     * Check if Locale class is enable
     * or not. 
     *
     * @return noolean
     */
    public function isSupport()
    {
        return class_exists('Locale');
    }

    /**
     * Get currency data
     *
     * @return string
     */
    public function get()
    {
        if (!$this->isSupport()) return $this->input;
        
        return $this->formatter->formatCurrency($this->input, $this->formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE));
    }

    /**
     * Get number formatter instance
     *
     * @return NumberFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Get currency based on ISO 4217
     * 
     * @source https://stackoverflow.com/questions/4299099/get-currency-iso-4217-code-based-on-locale#answer-47331489
     * @return array
     */
    public function getIsoCode()
    {
        if (!$this->isSupport()) return [['0', 'Extension Intl must be enable first']];

        // return list
        return array_filter(array_reduce(\ResourceBundle::getLocales(''), function (array $currencies, string $locale) {
            $currencies[$locale] = [\NumberFormatter::create(
                $locale,
                \NumberFormatter::CURRENCY
            )->getTextAttribute(\NumberFormatter::CURRENCY_CODE), locale_get_display_region($locale)];
        
            return $currencies;
        },[]), fn($code) => ($code[0] !== 'XXX'));
    }
}