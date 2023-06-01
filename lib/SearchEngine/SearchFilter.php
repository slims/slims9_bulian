<?php

namespace SLiMS\SearchEngine;

use SLiMS\DB;

trait SearchFilter
{
    protected $customFilter = [];

    public function setCustomFilter($filter)
    {
        $this->customFilter = $filter;
    }

    public function getFilter($opac, $build = false)
    {
        $filter = [];

        # Publish Year
        list($min, $max) = $this->getYears();
        $filter[] = [
            'header' => __('Publication Year'),
            'name' => 'years',
            'type' => 'range',
            'min' => $min,
            'max' => $max,
            'from' => $min,
            'to' => $max
        ];

        # Availability
        $filter[] = [
            'header' => __('Availability'),
            'name' => 'availability',
            'type' => 'radio',
            'items' => [
                [
                    'value' => '1',
                    'label' => __('On Shelf')
                ]
            ]
        ];

        # Attachment
        $filter[] = [
            'header' => __('Attachment'),
            'name' => 'attachment',
            'type' => 'checkbox',
            'items' => [
                [
                    'value' => 'pdf',
                    'label' => __('PDF')
                ],
                [
                    'value' => 'audio',
                    'label' => __('Audio')
                ],
                [
                    'value' => 'video',
                    'label' => __('Video')
                ]
            ]
        ];

        # Collection type
        $filter[] = [
            'header' => __('Collection Type'),
            'name' => 'colltype',
            'type' => 'checkbox',
            'items' => $this->getCollectionType()
        ];

        # GMD
        $filter[] = [
            'header' => __('General Material Designation'),
            'name' => 'gmd',
            'type' => 'checkbox',
            'items' => $this->getGMD()
        ];

        # Location
        $filter[] = [
            'header' => __('Location'),
            'name' => 'location',
            'type' => 'checkbox',
            'items' => $this->getLocation()
        ];

        # Language
        $filter[] = [
            'header' => __('Language'),
            'name' => 'lang',
            'type' => 'checkbox',
            'items' => $this->getLanguage()
        ];

        if ($build) return $this->buildFilter($opac, array_merge($filter, $this->customFilter));
        return array_merge($filter, $this->customFilter);
    }

    public function getYears()
    {
        $query = DB::getInstance()->query("select min(publish_year), max(publish_year) from biblio where length(publish_year) = 4 and substring(publish_year, 1,1) <= 2 and publish_year REGEXP '^-?[0-9]+$'");
        return $query->fetch(\PDO::FETCH_NUM);
    }

    public function getCollectionType()
    {
        $query = DB::getInstance()->query("select coll_type_id `value`, coll_type_name `label` from mst_coll_type");
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getGMD()
    {
        $query = DB::getInstance()->query("select gmd_id `value`, gmd_name `label` from mst_gmd");
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getLocation()
    {
        $query = DB::getInstance()->query("select location_id `value`, location_name `label` from mst_location order by location_name asc");
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getLanguage()
    {
        $query = DB::getInstance()->query("select language_id `value`, language_name `label` from mst_language order by language_name asc");
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buildFilter($opac, $filters): string
    {
        // get filter from url
        $filterStr = \utility::filterData('filter', 'get', false, true, true);
        $filterArr = json_decode($filterStr??'', true);

        $str = '<form id="search-filter"><ul class="list-group list-group-flush">';
        // $str .= '<input type="hidden" name="csrf_token" value="'.$opac->getCsrf().'">';

        foreach ($this->reOrder($filters) as $index => $filter) {
            if ($index < 1) {
                $str .= '<li class="list-group-item bg-transparent pl-0 border-top-0">';
            } else {
                $str .= '<li class="list-group-item bg-transparent pl-0">';
            }

            $str .= <<<HTML
                <div class="d-flex justify-content-between align-items-center cursor-pointer" data-toggle="collapse" data-target="#collapse-{$index}">
                    <strong class="text-sm">{$filter['header']}</strong>
                    <i class="dropdown-toggle"></i>
                </div>
                <div class="collapse show text-sm" id="collapse-{$index}"><div class="mt-2">
HTML;

            $value = $filterArr[$filter['name']] ?? null;

            switch ($filter['type']) {
                case 'range':
                    list($from, $to) = is_null($value) ? [$filter['from'], $filter['to']] : explode(';', $value);
                    $str .= <<<HTML
                        <input type="text" class="input-slider" name="{$filter['name']}" value=""
                               data-type="double"
                               data-min="{$filter['min']}"
                               data-max="{$filter['max']}"
                               data-from="{$from}"
                               data-to="{$to}"
                               data-grid="true"
                        />
HTML;
                    break;

                case 'radio':
                case 'checkbox':
                    foreach ($filter['items'] as $idx => $item) {
                        if (empty($item['value'])) continue;
                        $item_index = md5($filter['header'] . $item['value']);

                        if ($idx == 4) {
                            # open collapse items wrapper
                            $str .= '<div class="collapse" id="seeMore-' . $index . '">';
                        }

                        $filter_name = $filter['name'];
                        if($filter['type'] == 'checkbox') {
                            $filter_name .= '['.$idx.']';
                            $value = $filterArr[$filter['name'].'['.$idx.']'] ?? null;
                        } else {
                            $value = $filterArr[$filter['name']] ?? null;
                        }

                        # from advanced search
                        if (isset($_GET[$filter['name']]) && $_GET[$filter['name']] == $item['label'])
                            $value = $item['value'];

                        $checked = $value == $item['value'] ? 'checked' : '';
                        $clear = ($filter['clear'] ?? false) ? 'clear="'.$filter_name.'"' : '';

                        $str .= <<<HTML
                            <div class="form-check">
                                <input class="form-check-input" name="{$filter_name}" type="{$filter['type']}" 
                                    id="item-{$item_index}" value="{$item['value']}" {$checked} {$clear}>
                                <label class="form-check-label" for="item-{$item_index}">{$item['label']}</label>
                            </div>
HTML;
                    }
                    if (count($filter['items']) > 4) {
                        # close collapse items wrapper
                        $str .= '</div>';
                        $str .= '<a class="d-block mt-2" data-toggle="collapse" href="#seeMore-' . $index . '">' . __('See More') . '</a>';
                    }
                    break;
            }

            $str .= '</div></div></li>';
        }
        $str .= '</ul>';

        # prepare to sort
        $str .= '<input id="sort" name="sort" type="hidden" value="0" />';

        $str .= '</form>';
        return $str;
    }

    public function reOrder($filters)
    {
        $orderFilter = [];
        $match = [];
        foreach ($filters as $index => $filter) {
            if (in_array($filter['header'], $match)) continue;
            foreach ($filters as $checkOrderFilter) {
                if (isset($checkOrderFilter['before']) && $checkOrderFilter['before'] === $filter['header']) {
                    $orderFilter[$index] = $checkOrderFilter;
                    $orderFilter[($index + 1)] = $filter;
                    array_push($match, $checkOrderFilter['header'], $checkOrderFilter['before']);
                } elseif (isset($checkOrderFilter['after']) && $checkOrderFilter['after'] === $filter['header']) {
                    $orderFilter[$index] = $filter;
                    $orderFilter[($index + 1)] = $checkOrderFilter;
                    array_push($match, $checkOrderFilter['header'], $checkOrderFilter['after']);
                }
            }
            if (!in_array($filter['header'], $match)) $orderFilter[] = $filter;
        }

        return $orderFilter;
    }
}