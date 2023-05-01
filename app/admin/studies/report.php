<?php
namespace EdwardsEyes\admin\studies;

use EdwardsEyes\inc\database;

$accessLevel = intval($_SESSION['userinfo']['access']);

$connect = new database();
$studyId = null;
$studies = $connect->getStudiesFor($_SESSION['userinfo']['id']);

foreach ($studies as $studyIdIdx => $studyDetails) {
    if ($studyDetails['studyidnum'] === $studyDetails['coordinating']) {
        $studyId = $studyIdIdx;
    }
}

if (!empty($studies[$studyId]['studyname'])) {
    $studyName = str_replace(' ', '-', $studies[$studyId]['studyname']);
} else {
    $studyName = $studyId;
}
if ($accessLevel < array_search('coordinator', ACL_RANKS)) {
    header('Location: ' . ROOT_FOLDER . '/admin/dashboard.php');
    exit();
}


$studyInfo = $connect->getStudy($studyId, $accessLevel);
if (!empty($studyInfo)) {
    set_time_limit(0);
    ini_set('memory_limit', '255M');
    $headersSent = false;
    $data = $connect->reportStudy($studyId);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=study-' . $studyName . '-report-' . date('Y-m-d_H_i_s'). '.csv');
    $out = fopen('php://output', 'w');

    foreach ($data as $key => $val) {
        $furrows =  (strtoupper(@$val['furrows_1']) == 'Y')?1:0;
        $furrows += (strtoupper(@$val['furrows_2']) == 'Y')?1:0;
        $furrows += (strtoupper(@$val['furrows_3']) == 'Y')?1:0;
        $furrows += (strtoupper(@$val['furrows_4']) == 'Y')?1:0;
        if ($furrows == 0) {
            $furrows = 0;
        } elseif (strtoupper(@$val['furrows_50']) == 'N') {
            $furrows = 1;
        } else {
            $furrows = 2;
        }


        $furrowsIr =  (strtoupper(@$val['otherIr']['furrows_1']) == 'Y')?1:0;
        $furrowsIr += (strtoupper(@$val['otherIr']['furrows_2']) == 'Y')?1:0;
        $furrowsIr += (strtoupper(@$val['otherIr']['furrows_3']) == 'Y')?1:0;
        $furrowsIr += (strtoupper(@$val['otherIr']['furrows_4']) == 'Y')?1:0;
        if ($furrowsIr == 0) {
            $furrowsIr = 0;
        } elseif (strtoupper(@$val['otherIr']['furrows_50']) == 'N') {
            $furrowsIr = 1;
        } else {
            $furrowsIr = 2;
        }

        $wolfflin =  (strtoupper(@$val['wolfflin_1']) == 'Y')?1:0;
        $wolfflin += (strtoupper(@$val['wolfflin_2']) == 'Y')?1:0;
        $wolfflin += (strtoupper(@$val['wolfflin_3']) == 'Y')?1:0;
        $wolfflin += (strtoupper(@$val['wolfflin_4']) == 'Y')?1:0;
        if ($wolfflin == 0) {
            $wolfflin = 0;
        } elseif (strtoupper(@$val['wolfflin_50']) == 'N') {
            $wolfflin = 1;
        } else {
            $wolfflin = 2;
        }
        $wolfflinIr =  (strtoupper(@$val['otherIr']['wolfflin_1']) == 'Y')?1:0;
        $wolfflinIr += (strtoupper(@$val['otherIr']['wolfflin_2']) == 'Y')?1:0;
        $wolfflinIr += (strtoupper(@$val['otherIr']['wolfflin_3']) == 'Y')?1:0;
        $wolfflinIr += (strtoupper(@$val['otherIr']['wolfflin_4']) == 'Y')?1:0;
        if ($wolfflinIr == 0) {
            $wolfflinIr = 0;
        } elseif (strtoupper(@$val['otherIr']['wolfflin_50']) == 'N') {
            $wolfflinIr = 1;
        } else {
            $wolfflinIr = 2;
        }

        $thisRecord = array(
            'participant' => @$val['participant'],
            'reviewer' => @$val['username'],
            'Included' => (strtoupper(@$val['useeye']) == 'Y')?1:0,
            'Filename' => @$val['filename'],
            'Obstructed' => (strtoupper(@$val['obstructed']) == 'Y')?1:0,
            'Iris Centre X' => @$val['iris_x'],
            'Iris Centre Y' => @$val['iris_y'],
            'Iris Centre' => "(" . @$val['iris_x'] .", ". @$val['iris_y'] .")",
            'Iris Radius' => @$val['iris_r'],
            'Iris Circumference' => @$val['iris_r'] * 2,
            'Pupil Centre X' => @$val['pupil_x'],
            'Pupil Centre Y' => @$val['pupil_y'],
            'Pupil Centre' => "(" . @$val['pupil_x'] .", ". @$val['pupil_y'] .")",
            'Pupil Radius' => @$val['pupil_r'],
            'Pupil Circumference' => @$val['pupil_r'] * 2,
            'Pupil Offset' => sqrt(pow(@$val['pupil_x']-@$val['iris_x'], 2)+pow(@$val['pupil_y']- @$val['iris_y'], 2)),
            'Pupil Quadrant' => $connect->findQuadrant(
                floatval(@$val['iris_x']), 
                floatval(@$val['iris_y']), 
                floatval(@$val['pupil_x']), 
                floatval(@$val['pupil_y'])
            ),
            'Collarette Radius' => @$val['collarette_r'],
            'Collarette Circumference' => @$val['collarette_r'] * 2,
            'Contraction Furrow Category' => $furrows,
            'Contraction Furrow Over Half' => (strtoupper(@$val['furrows_50']) == 'Y')?1:0,
            'Contraction Furrow in Alpha (3)' => (strtoupper(@$val['furrows_1']) == 'Y')?1:0,
            'Contraction Furrow in Beta (2)' => (strtoupper(@$val['furrows_2']) == 'Y')?1:0,
            'Contraction Furrow in Delta (1)' => (strtoupper(@$val['furrows_3']) == 'Y')?1:0,
            'Contraction Furrow in Gamma (4)' => (strtoupper(@$val['furrows_4']) == 'Y')?1:0,

            'Contraction Furrow Category (IR)' => $furrowsIr,
            'Contraction Furrow Over Half (IR)' => (strtoupper(@$val['otherIr']['furrows_50']) == 'Y')?1:0,
            'Contraction Furrow in Alpha (3) (IR)' => (strtoupper(@$val['otherIr']['furrows_1']) == 'Y')?1:0,
            'Contraction Furrow in Beta (2) (IR)' => (strtoupper(@$val['otherIr']['furrows_2']) == 'Y')?1:0,
            'Contraction Furrow in Delta (1) (IR)' => (strtoupper(@$val['otherIr']['furrows_3']) == 'Y')?1:0,
            'Contraction Furrow in Gamma (4) (IR)' => (strtoupper(@$val['otherIr']['furrows_4']) == 'Y')?1:0,

            'Wolfflin Nodules Category' => $wolfflin,
            'Wolfflin Nodules Over Half' => (strtoupper(@$val['wolfflin_50']) == 'Y')?1:0,
            'Wolfflin Nodules in Alpha (3)' => (strtoupper(@$val['wolfflin_1']) == 'Y')?1:0,
            'Wolfflin Nodules in Beta (2)' => (strtoupper(@$val['wolfflin_2']) == 'Y')?1:0,
            'Wolfflin Nodules in Delta (1)' => (strtoupper(@$val['wolfflin_3']) == 'Y')?1:0,
            'Wolfflin Nodules in Gamma (4)' => (strtoupper(@$val['wolfflin_4']) == 'Y')?1:0,

            'Wolfflin Nodules Category (IR)' => $wolfflinIr,
            'Wolfflin Nodules Over Half (IR)' => (strtoupper(@$val['otherIr']['wolfflin_50']) == 'Y')?1:0,
            'Wolfflin Nodules in Alpha (3) (IR)' => (strtoupper(@$val['otherIr']['wolfflin_1']) == 'Y')?1:0,
            'Wolfflin Nodules in Beta (2) (IR)' => (strtoupper(@$val['otherIr']['wolfflin_2']) == 'Y')?1:0,
            'Wolfflin Nodules in Delta (1) (IR)' => (strtoupper(@$val['otherIr']['wolfflin_3']) == 'Y')?1:0,
            'Wolfflin Nodules in Gamma (4) (IR)' => (strtoupper(@$val['otherIr']['wolfflin_4']) == 'Y')?1:0,

            'Ring around the Sclera' => (strtoupper(@$val['scleraRing']) == 'Y')?1:0,
            'Pigment Spots on the Sclera' => (strtoupper(@$val['scleraSpots']) == 'Y')?1:0,
            'Sclera Pigmentation' => (strtoupper(@$val['scleraRing']) == 'Y' || strtoupper(@$val['scleraSpots']) == 'Y')?1:0,

            'Nevi Category' => @$val['nevicategory'],
            'Small Nevi' => @$val['nevisize_s'],
            'Large Nevi' => @$val['nevisize_l'],
            'Total Nevi' => @$val['nevisize_s'] + @$val['nevisize_l'],
            'Small Nevi Quadrant in Alpha (3)' => @$val['neviquadrant_a']['size_s'],
            'Large Nevi Quadrant in Alpha (3)' => @$val['neviquadrant_a']['size_l'],
            'Nevi Quadrant in Alpha (3)' => (array_sum((array)@$val['neviquadrant_a']) > 0)?1:0,
            'Small Nevi Quadrant in Beta (2)' => @$val['neviquadrant_b']['size_s'],
            'Large Nevi Quadrant in Beta (2)' => @$val['neviquadrant_b']['size_l'],
            'Nevi Quadrant in Beta (2)' => (array_sum((array)@$val['neviquadrant_b']) > 0)?1:0,
            'Small Nevi Quadrant in Delta (1)' => @$val['neviquadrant_d']['size_s'],
            'Large Nevi Quadrant in Delta (1)' => @$val['neviquadrant_d']['size_l'],
            'Nevi Quadrant in Delta (1)' => (array_sum((array)@$val['neviquadrant_d']) > 0)?1:0,
            'Small Nevi Quadrant in Gamma (4)' => @$val['neviquadrant_g']['size_s'],
            'Large Nevi Quadrant in Gamma (4)' => @$val['neviquadrant_g']['size_l'],
            'Nevi Quadrant in Gamma (4)' => (array_sum((array)@$val['neviquadrant_g']) > 0)?1:0,

            'Nevi Category (IR)' => @$val['neviIrcategory'],
            'Small Nevi (IR)' => @$val['neviIrsize_s'],
            'Large Nevi (IR)' => @$val['neviIrsize_l'],
            'Total Nevi (IR)' => @$val['neviIrsize_s'] + @$val['neviIrsize_l'],
            'Small Nevi Quadrant in Alpha (3) (IR)' => @$val['neviIrquadrant_a']['size_s'],
            'Large Nevi Quadrant in Alpha (3) (IR)' => @$val['neviIrquadrant_a']['size_l'],
            'Nevi Quadrant in Alpha (3) (IR)' => (array_sum((array)@$val['neviIrquadrant_a']) > 0)?1:0,
            'Small Nevi Quadrant in Beta (2) (IR)' => @$val['neviIrquadrant_b']['size_s'],
            'Large Nevi Quadrant in Beta (2) (IR)' => @$val['neviIrquadrant_b']['size_l'],
            'Nevi Quadrant in Beta (2) (IR)' => (array_sum((array)@$val['neviIrquadrant_b']) > 0)?1:0,
            'Small Nevi Quadrant in Delta (1) (IR)' => @$val['neviIrquadrant_d']['size_s'],
            'Large Nevi Quadrant in Delta (1) (IR)' => @$val['neviIrquadrant_d']['size_l'],
            'Nevi Quadrant in Delta (1) (IR)' => (array_sum((array)@$val['neviIrquadrant_d']) > 0)?1:0,
            'Small Nevi Quadrant in Gamma (4) (IR)' => @$val['neviIrquadrant_g']['size_s'],
            'Large Nevi Quadrant in Gamma (4) (IR)' => @$val['neviIrquadrant_g']['size_l'],
            'Nevi Quadrant in Gamma (4) (IR)' => (array_sum((array)@$val['neviIrquadrant_g']) > 0)?1:0,

            'Crypt Category' => @$val['cryptcategory'],
            'Inner Crypts' => @$val['cryptsize_f'],
            'Small Crypts' => @$val['cryptsize_s'],
            'Large Crypts' => @$val['cryptsize_l'],
            'Inner Crypts Quadrant in Alpha (3)' => @$val['cryptquadrant_a']['size_f'],
            'Small Crypts Quadrant in Alpha (3)' => @$val['cryptquadrant_a']['size_s'],
            'Large Crypts Quadrant in Alpha (3)' => @$val['cryptquadrant_a']['size_l'],
            'Large Crypts Present in Quadrant in Alpha (3)' => (@$val['cryptquadrant_a']['size_l'] > 0)?1:0,
            'Inner Crypts Quadrant in Beta (2)' => @$val['cryptquadrant_b']['size_f'],
            'Small Crypts Quadrant in Beta (2)' => @$val['cryptquadrant_b']['size_s'],
            'Large Crypts Quadrant in Beta (2)' => @$val['cryptquadrant_b']['size_l'],
            'Large Crypts Present in Quadrant in Beta (2)' => (@$val['cryptquadrant_b']['size_l'] > 0)?1:0,
            'Inner Crypts Quadrant in Delta (1)' => @$val['cryptquadrant_d']['size_f'],
            'Small Crypts Quadrant in Delta (1)' => @$val['cryptquadrant_d']['size_s'],
            'Large Crypts Quadrant in Delta (1)' => @$val['cryptquadrant_d']['size_l'],
            'Large Crypts Present in Quadrant in Delta (1)' => (@$val['cryptquadrant_d']['size_l'] > 0)?1:0,
            'Inner Crypts Quadrant in Gamma (4)' => @$val['cryptquadrant_g']['size_f'],
            'Small Crypts Quadrant in Gamma (4)' => @$val['cryptquadrant_g']['size_s'],
            'Large Crypts Quadrant in Gamma (4)' => @$val['cryptquadrant_g']['size_l'],
            'Large Crypts Present in Quadrant in Gamma (4)' => (@$val['cryptquadrant_g']['size_l'] > 0)?1:0,

            'Crypt Category (IR)' => @$val['cryptIrcategory'],
            'Inner Crypts (IR)' => @$val['cryptIrsize_f'],
            'Small Crypts (IR)' => @$val['cryptIrsize_s'],
            'Large Crypts (IR)' => @$val['cryptIrsize_l'],
            'Inner Crypts Quadrant in Alpha (3) (IR)' => @$val['cryptIrquadrant_a']['size_f'],
            'Small Crypts Quadrant in Alpha (3) (IR)' => @$val['cryptIrquadrant_a']['size_s'],
            'Large Crypts Quadrant in Alpha (3) (IR)' => @$val['cryptIrquadrant_a']['size_l'],
            'Large Crypts Present in Quadrant in Alpha (3) (IR)' => (@$val['cryptIrquadrant_a']['size_l'] > 0)?1:0,
            'Inner Crypts Quadrant in Beta (2) (IR)' => @$val['cryptIrquadrant_b']['size_f'],
            'Small Crypts Quadrant in Beta (2) (IR)' => @$val['cryptIrquadrant_b']['size_s'],
            'Large Crypts Quadrant in Beta (2) (IR)' => @$val['cryptIrquadrant_b']['size_l'],
            'Large Crypts Present in Quadrant in Beta (2) (IR)' => (@$val['cryptIrquadrant_b']['size_l'] > 0)?1:0,
            'Inner Crypts Quadrant in Delta (1) (IR)' => @$val['cryptIrquadrant_d']['size_f'],
            'Small Crypts Quadrant in Delta (1) (IR)' => @$val['cryptIrquadrant_d']['size_s'],
            'Large Crypts Quadrant in Delta (1) (IR)' => @$val['cryptIrquadrant_d']['size_l'],
            'Large Crypts Present in Quadrant in Delta (1) (IR)' => (@$val['cryptIrquadrant_d']['size_l'] > 0)?1:0,
            'Inner Crypts Quadrant in Gamma (4) (IR)' => @$val['cryptIrquadrant_g']['size_f'],
            'Small Crypts Quadrant in Gamma (4) (IR)' => @$val['cryptIrquadrant_g']['size_s'],
            'Large Crypts Quadrant in Gamma (4) (IR)' => @$val['cryptIrquadrant_g']['size_l'],
            'Large Crypts Present in Quadrant in Gamma (4) (IR)' => (@$val['cryptIrquadrant_g']['size_l'] > 0)?1:0,

            'Total Pixels Counted' => @$val['rgb']['total_n'],
            'Average Red' => @$val['rgb']['total_r'],
            'Average Green' => @$val['rgb']['total_g'],
            'Average Blue' => @$val['rgb']['total_bl'],
            'Average L' => @$val['rgb']['total_l'],
            'Average a' => @$val['rgb']['total_a'],
            'Average b' => @$val['rgb']['total_b'],

            'Delta E 2000' => @$val['rgb']['deltae'],
            'Collarette Pixels Counted' => @$val['rgb']['inner_n'],
            'Collarette Red' => @$val['rgb']['inner_r'],
            'Collarette Green' => @$val['rgb']['inner_g'],
            'Collarette Blue' => @$val['rgb']['inner_bl'],
            'Collarette L' => @$val['rgb']['inner_l'],
            'Collarette a' => @$val['rgb']['inner_a'],
            'Collarette b' => @$val['rgb']['inner_b'],

            'Outer Iris Pixels Counted' => @$val['rgb']['outer_n'],
            'Outer Iris Red' => @$val['rgb']['outer_r'],
            'Outer Iris Green' => @$val['rgb']['outer_g'],
            'Outer Iris Blue' => @$val['rgb']['outer_bl'],
            'Outer Iris L' => @$val['rgb']['outer_l'],
            'Outer Iris a' => @$val['rgb']['outer_a'],
            'Outer Iris b' => @$val['rgb']['outer_b']);
        switch ($studies[$studyId]['additional_data']) {
            case 'pigmentation_study':
                $thisRecord = array_merge($thisRecord, array(
                    'dob' => @$val['dob'],
                    'dop' => @$val['dop'],
                    'age' => @$val['age'],
                    'sexcode_(1=female,_2=male)' => @$val['sexcode_(1=female,_2=male)'],
                    'ancestry' => @$val['ancestry'],
                    'ancestry_code' => @$val['ancestry_code'],
                    'place_of_birth' => @$val['place_of_birth'],
                    'ethnic_background' => @$val['ethnic_background'],
                    'current_residence' => @$val['current_residence'],
                    'first_language' => @$val['first_language'],
                    'other_languages' => @$val['other_languages'],
                    'mother_(ancestry_(first_language,_place_of_birth))' => @$val['mother_(ancestry_(first_language,_place_of_birth))'],
                    'maternal_grandmother_(ancestry_(first_language,_place_of_birth))' => @$val['maternal_grandmother_(ancestry_(first_language,_place_of_birth))'],
                    'maternal_grandfather_(ancestry_(first_language,_place_of_birth))' => @$val['maternal_grandfather_(ancestry_(first_language,_place_of_birth))'],
                    'father_(ancestry_(first_language,_place_of_birth))' => @$val['father_(ancestry_(first_language,_place_of_birth))'],
                    'paternal_grandmother_(ancestry_(first_language,_place_of_birth))' => @$val['paternal_grandmother_(ancestry_(first_language,_place_of_birth))'],
                    'paternal_grandfather_(ancestry_(first_language,_place_of_birth))' => @$val['paternal_grandfather_(ancestry_(first_language,_place_of_birth))'],
                    'weight_(lbs)' => @$val['weight_(lbs)'],
                    'height_(cm)' => @$val['height_(cm)'],
                    'melanin_1_average_(arm)' => @$val['melanin_1_average_(arm)'],
                    'erythema_1_average_(arm)' => @$val['erythema_1_average_(arm)'],
                    'melanin_1_average_(hair)' => @$val['melanin_1_average_(hair)'],
                    'erythema_1_average_(hair)' => @$val['erythema_1_average_(hair)'],
                    'melanin_2_average_(arm)' => @$val['melanin_2_average_(arm)'],
                    'erythema_2_average_(arm)' => @$val['erythema_2_average_(arm)'],
                    'melanin_2_average_(hair)' => @$val['melanin_2_average_(hair)'],
                    'erythema_2_average_(hair)' => @$val['erythema_2_average_(hair)'],
                    'tanning_salon_(2=no,_1=yes)' => @$val['tanning_salon_(2=no,_1=yes)'],
                    'sunny_destination_(2=no,_1=yes)' => @$val['sunny_destination_(2=no,_1=yes)'],
                    'hair_dye_(2=no,_1=yes,_3=dyed_3+_months_back)' => @$val['hair_dye_(2=no,_1=yes,_3=dyed_3+_months_back)'],
                    'eye_color' => @$val['eye_color'],
                    'fitzpatrick_skin_type' => @$val['fitzpatrick_skin_type'],
                    'permission_to_use_dna_(1-yes,_2-no)' => @$val['permission_to_use_dna_(1-yes,_2-no)'],
                    'rs6265' => @$val['rs6265'],
                    'rs4778138' => @$val['rs4778138'],
                    'rs7495174' => @$val['rs7495174'],
                    'rs12913832' => @$val['rs12913832'],
                    'rs1800407' => @$val['rs1800407'],
                    'rs12896399' => @$val['rs12896399'],
                    'rs16891982' => @$val['rs16891982'],
                    'rs1393350' => @$val['rs1393350'],
                    'rs1126809' => @$val['rs1126809'],
                    'rs12203592' => @$val['rs12203592'],
                    'rs3739070' => @$val['rs3739070'],
                    'rs10235789' => @$val['rs10235789'],
                    'rs13140875' => @$val['rs13140875'],
                    'rs7277820' => @$val['rs7277820'],
                    'rs1408799' => @$val['rs1408799'],
                    'rs1426654' => @$val['rs1426654'],
                    'rs9894429' => @$val['rs9894429'],
                    'rs3768056' => @$val['rs3768056'],
                    'rs6058017' => @$val['rs6058017'],
                    'rs4778241' => @$val['rs4778241'],
                    'rs11630290' => @$val['rs11630290'],
                    'rs17712299' => @$val['rs17712299'],
                    'rs17132289' => @$val['rs17132289'],
                    'rs2889732' => @$val['rs2889732'],
                    'rs13036385' => @$val['rs13036385'],
                    'rs6746030' => @$val['rs6746030'],
                    'rs9406647' => @$val['rs9406647'],
                    'rs1799971' => @$val['rs1799971'],
                    'rs1800414' => @$val['rs1800414'],
                    'rs7497270' => @$val['rs7497270'],
                    'rs74653330' => @$val['rs74653330'],
                    'rs3739714' => @$val['rs3739714'],
                    'rs10756778' => @$val['rs10756778'],
                    'rs10962591' => @$val['rs10962591'],
                    'rs2227983' => @$val['rs2227983'],
                    'rs12668421' => @$val['rs12668421'],
                    'rs6917661' => @$val['rs6917661'],
                    'rs885479' => @$val['rs885479'],
                    'rs33932559' => @$val['rs33932559'],
                    'rs2292881' => @$val['rs2292881'],
                    'rs3809578' => @$val['rs3809578'],
                    'rs3754234' => @$val['rs3754234'],
                    'rs4659610' => @$val['rs4659610'],
                    'rs7522053' => @$val['rs7522053'],
                    'rs80350829' => @$val['rs80350829'],
                    'rs4930046' => @$val['rs4930046'],
                    ));
                    // No break
            case 'cincinnati':
                $thisRecord = array_merge($thisRecord, array(
                    'ancestry' => @$val['ancestry']
                ));
        }
        if (!$headersSent) {
            $headersSent = true;
            fputcsv($out, array_keys($thisRecord));
        }
        fputcsv($out, $thisRecord);
    }
    fclose($out);

/*     echo '<table border=1>';
    foreach ($temp as $d) {
        echo '<tr>';
        foreach ($d as $c) {
            echo "<td>$c</td>";
        }
        echo '</tr>';
    }
    echo '</table>';
 */
} else {
    echo 'Access denied';
}
