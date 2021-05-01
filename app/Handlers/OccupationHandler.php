<?php

namespace App\Handlers;

class OccupationHandler
{
    /**
     * @param $occupation_1
     * @param $occupation_2
     * @return array
     */
    public function getCompareResult($occupation_1, $occupation_2)
    {
        //Get all label values for first input
        $labels_1 = array_column($occupation_1, 'label');

        //Get all label values for second input
        $labels_2 = array_column($occupation_2, 'label');

        $total_matches = 0;
        $result = $temp = $labels = [];

        /*
        checking in two inputs, if the the value of array input exists in both inputs
         */
        foreach ($occupation_1 as $item) {
            if (in_array($item['label'], $labels_2)) {
                $temp[$item['label']][]= $item;
                $total_matches++;
            }
        }

        foreach ($occupation_2 as $item) {
            if (in_array($item['label'], $labels_1)) {
                $temp[$item['label']][]= $item;
                $labels[]=$item['label'];
                $total_matches++;
            }
        }

        foreach($labels as $label){
            if(isset($temp[$label])){
                $result[][$label] = $temp[$label];
            }
        }
        $record_count_in_both_occupations = (count($occupation_1)+count($occupation_2));

        $match = $record_count_in_both_occupations > 0 ?
            ($total_matches/$record_count_in_both_occupations)*100 : 0;

        return [
            floor($match),
            $result
        ];
    }
}