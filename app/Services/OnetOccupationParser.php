<?php

namespace App\Services;

use App\Contracts\OccupationParser;
use Illuminate\Support\Facades\Cache;
use PHPHtmlParser\Dom;

class OnetOccupationParser implements OccupationParser
{
    const CACHE_OCCUPATION_CODE_WITH_SKILLS = 'occupation_code_with_skills';

    private $scope = '';

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return ucfirst(str_plural(strtolower($this->scope)));
    }

    public function getUrl($occupation_code)
    {
        return 'https://www.onetonline.org/link/details/' . $occupation_code;
    }

    public function list()
    {
        $json = file_get_contents(storage_path('/app/onet_occupations.json'));
        return json_decode($json, true);
    }

    public function get($occupation_code)
    {
        //find code in cache
        $cache_values = Cache::has(self::CACHE_OCCUPATION_CODE_WITH_SKILLS) ?
            Cache::get(self::CACHE_OCCUPATION_CODE_WITH_SKILLS) : [];

        if (array_key_exists($occupation_code, $cache_values)) {
            //if found use that
            $occupation_skills = $cache_values[$occupation_code];
        } else {
            $occupation_skills = $this->getFromDom($occupation_code);
            //else add to cache and use
            $cache_values = array_merge($cache_values,[ $occupation_code => $occupation_skills]);
            Cache::forever(self::CACHE_OCCUPATION_CODE_WITH_SKILLS, $cache_values);
        }
        return $occupation_skills;
    }

    private function getFromDom($occupation_code)
    {
        $dom = new Dom();
        $url = $this->getUrl($occupation_code);
        $dom->loadFromUrl($url, [
            'removeScripts' => true,
            'removeStyles' => true
        ]);

        $items = [];
        $rows = $dom->find('.section_' . $this->getScope() . ' table tr');

        foreach ($rows as $row) {
            $value_el = $row->find('.report2a b');
            $value = $value_el->count() ? ($value_el[0])->text : null;
            $label_el = $row->find('.report2 .moreinfo b');
            $label = $label_el->count() ? ($label_el[0])->text : null;
            $description_el = $row->find('.report2 .moreinfo');
            $description = $description_el->count() ? ($description_el[0])->text : null;

            if ($value && $label) {
                $items[] = [
                    'label' => trim($label),
                    'value' => $value,
                    'description' => trim(str_replace(['&#8212;'], '', $description))
                ];

            }
        }

        return $items;
    }
}