<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-10 23:32:04
 * @modify date 2022-10-23 16:02:21
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

class Currency
{
    private $input;
    private ?object $formatter = null;

    public function __construct($input = 0)
    {
        $this->input = $input;
        if ($this->isSupport()) $this->formatter = new \NumberFormatter(config('custom_currency_locale.region', config('default_lang')), \NumberFormatter::CURRENCY);
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
        
        // override default value
        $custom = config('custom_currency_locale');

        // enable or not
        if (isset($custom['enable']) && !(bool)$custom['enable']) return $this->input;

        if (!is_null($custom))
        {
            foreach ($custom['detail']??[] as $property => $data) {
                foreach ($data as $const => $value) {
                    call_user_func_array([$this->formatter, 'set' . ucfirst($property)], [constant('\NumberFormatter::'. $const), $value]);
                }
            }
        }

        return $this->formatter->formatCurrency($this->input??0, $this->formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE));
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
        $list = array_filter(array_reduce(\ResourceBundle::getLocales(''), function (array $currencies, string $locale) {
            $code = \NumberFormatter::create(
                $locale,
                \NumberFormatter::CURRENCY
            )->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
            $currencies[$locale] = [$locale, locale_get_display_region($locale) . ' - ' . $locale . ' - ' . $code];
        
            return $currencies;
        },[]), fn($code) => (!preg_match('/XXX/i', $code[1])));

        sort($list);

        return $list;
    }

    /**
     * Convert object to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->get();
    }
}