<?php

function calcSinglePerformance($rank, $times, $standings) {
    $roundRank = ($rank + $rank + $times - 1) / 2;

    $l = 0;
    $r = 6000;
    
    while ($l < $r) {
        $mid = floor(($l + $r) / 2) + 1;
        $res = 0;

        foreach ($standings as $particular) {
            $res += 1 / (1 + pow(6.0, ($mid - $particular[2][2]) / 400));
        }

        if ($res > $roundRank - 0.5) {
            $l = $mid;
        } else {
            $r = $mid - 1;
        }
    }

    return $mid;
}

function calcPerformance($standings, $contests) {
    $extra_config = $contests["extra_config"];

    if (!isset($extra_config["contest_version"])) {
        $extra_config["contest_version"] = 2;
    }

    switch ($extra_config["contest_version"]) {
        case 1: $base_performance = 1200; $rated_bound = 10000; break; 
        case 2: $base_performance = 1000; $rated_bound = 2800; break;
        case 3: $base_performance = 800; $rated_bound = 2000; break;
    }

    $rankArray = array();

    for ($i=0; $i < count($standings); $i++) {
        if ($standings[$i][2][2] == "-1") {
            $standings[$i][2][2] = $base_performance;
            $standings[$i][2][4] = true;
        } else {
            $standings[$i][2][4] = false;
        }
        $rankArray[$standings[$i][3]] += 1;
    }

    $rank = 1;
    $performance = array();

    foreach ($standings as $particular) {
        $singlePerformance = calcSinglePerformance($particular[3], $rankArray[$particular[3]], $standings);

        if ($particular[2][4]) {
            $singlePerformance = ($singlePerformance - $base_performance) * 1.75 + $base_performance;
        }

        $singlePerformance = min($singlePerformance, $rated_bound + 400);
        $performance[$rank - 1] = floor($singlePerformance);

        $rank += 1;
    }

    return $performance;
}

function calcF($n) {
    $res1 = $res2 = 1;
    for ($i=1; $i<=$n; $i++) {
        $res1 += pow(0.81, $i);
        $res2 += pow(0.9, $i);
    }
    return sqrt($res1) / $res2;
}

function calcRating($standings, $contests){
    $performance = calcPerformance($standings, $contests);
    $rating = array();

    $rank = 1;
    $baseINF = calcF(1000);

    foreach ($standings as $particular) {
        $contests_history = DB::selectAll("select * from contests_history where username = '{$particular[2][0]}' order by calc_time desc, contest_id desc");

        $denominator = pow(0.9, 1);
        $averagePerformance = $performance[$rank - 1] * pow(0.9, 1);
        $singleRating = pow(2.0, $performance[$rank - 1] / 800) * pow(0.9, 1);

        for ($i = 0; $i < count($contests_history); $i++) {
            $averagePerformance += $contests_history[$i]["performance"] * pow(0.9, $i + 2);
            $singleRating += pow(2.0, $contests_history[$i]["performance"] / 800) * pow(0.9, $i+2);
            $denominator += pow(0.9, $i + 2);
        }

        $averagePerformance = floor($averagePerformance / $denominator);
        $singleRating = $singleRating / $denominator;
        $singleRating = log($singleRating, 2) * 800;

        $roundMinus = ((calcF(count($contests_history)+1)-$baseINF) / (calcF(1)-$baseINF)) * 1200;

        $singleRating = $singleRating - $roundMinus;
        
        if ($singleRating < 200) {
            $singleRating = pow(1.003, $singleRating - 372.71) / log(1.003, exp(1)) + 1;
        }


        DB::update($sql="update user_info SET performance = {$averagePerformance} where username = '{$particular[2][0]}'");
        DB::insert("insert into contests_history (username, contest_id, performance) VALUES ('{$particular[2][0]}', '{$contests["id"]}', '{$performance[$rank - 1]}')");

        $rating[$rank - 1] = floor($singleRating);
        $rank += 1;
    }

    return $rating;
}

function calcRatingGroup($rating) {
    switch ($rating) {
        case $rating >= 2200: return 1;
        case $rating >= 1900: return 2;
        case $rating >= 1600: return 3;
        case $rating >= 1300: return 4;
        case $rating >= 1000: return 5;
        case $rating >= 600: return 6;
        case $rating >= 200: return 7;
        case $rating > 0: return 8;
        default: return 9; 
    }
}