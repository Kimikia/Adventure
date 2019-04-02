<?php
include("FileReader.php");
include("data.php");

global $width;
global $height;


$caseList = initList();
$tour = 0;
$shouldContinue = true;
while ($shouldContinue) {
    $tourResults = jouerTour($caseList, $tour);
    $caseList = $tourResults[0];
    $shouldContinue = $tourResults[1];
    $tour++;
}
printResult($caseList, $height, $width);
FileReader::exportToFile($caseList);


function printResult($caseList, $height, $width)
{
    echo "\n ***************PRINTING RESULTS*********************\n";
    for ($i = 0; $i < $height; $i++) {
        for ($j = 0; $j < $width; $j++) {
            $case = getCaseByXY($caseList, $j, $i);
            printInfo($case);
        }
        echo "\n";
    }
    echo "\n";
}

function printInfo($case)
{
    if ($case) { //if not null
        $case->print();
    } else {
        Cases::printPlain();
    }
}

function getCaseByXY($caseList, $x, $y)
{
    foreach ($caseList as $case) {
        if ($case->x == $x && $case->y == $y) {
            if (Cases::isOrc($case) || Cases::isGoblin($case)) {
                if ($case->isDead) {
                   continue;
                }
            }

            return $case;
        }
    }

    return null;
}

function removeFromList(array &$caseList, Cases $case)
{
    foreach ($caseList as $elementKey => $element) {
        if ($element == $case) {
            unset($caseList[$elementKey]);
        }
    }
}

function avancerAventurier(&$caseList, Adventurer $adventurer)
{
    $newX = $adventurer->x;
    $newY = $adventurer->y;
    switch ($adventurer->orientation) {
        case ORIENTATION::ORIENTATION_EST:
            $newX++;
            break;
        case ORIENTATION::ORIENTATION_NORD:
            $newY--;
            break;
        case ORIENTATION::ORIENTATION_OUEST :
            $newX--;
            break;
        case ORIENTATION::ORIENTATION_SUD :
            $newY++;
            break;
    }
    $case = getCaseByXY($caseList, $newX, $newY);
    if (Cases::isMountain($case) || Cases::isAdventurer($case)) {
        //nothing changes
    } else if (Cases::isTreasure($case)) {
        if ($case->treasureCount > 0) {
            if ($case->treasureCount == 1) {
                removeFromList($caseList, $case);
            } else {
                $case->treasureCount--;
            }

            $adventurer->level++;
        }

        $adventurer->x = $newX;
        $adventurer->y = $newY;
    } else if (Cases::isGoblin($case) || Cases::isOrc($case)) {
        if ($adventurer->level >= $case->level || $case->isDead) {
            $case->isDead = true;
            $adventurer->x = $newX;
            $adventurer->y = $newY;
        } else {
            $adventurer->isDead = true;
        }
    } else {
        $adventurer->x = $newX;
        $adventurer->y = $newY;
    }
}

function droite(Adventurer $adventurer)
{
    switch ($adventurer->orientation) {
        case ORIENTATION::ORIENTATION_SUD:
            $adventurer->orientation = ORIENTATION::ORIENTATION_OUEST;
            break;
        case ORIENTATION::ORIENTATION_NORD:
            $adventurer->orientation = ORIENTATION::ORIENTATION_EST;
            break;
        case ORIENTATION::ORIENTATION_EST :
            $adventurer->orientation = ORIENTATION::ORIENTATION_SUD;
            break;
        case ORIENTATION::ORIENTATION_OUEST:
            $adventurer->orientation = ORIENTATION::ORIENTATION_NORD;
            break;
    }

}

function gauche(Adventurer $case)
{
    switch ($case->orientation) {
        case ORIENTATION::ORIENTATION_SUD:
            $case->orientation = ORIENTATION::ORIENTATION_EST;
            break;
        case ORIENTATION::ORIENTATION_NORD:
            $case->orientation = ORIENTATION::ORIENTATION_OUEST;
            break;
        case ORIENTATION::ORIENTATION_EST :
            $case->orientation = ORIENTATION::ORIENTATION_NORD;
            break;
        case ORIENTATION::ORIENTATION_OUEST:
            $case->orientation = ORIENTATION::ORIENTATION_SUD;
    }
}

function jouerTour($caseList, $tour)
{
    $stillHaveSequence = false;
    foreach ($caseList as $case) {
        if (Cases::isAdventurer($case)) {
            if ($tour < strlen($case->sequenceDeMouvement)) {
                $stillHaveSequence = true;
                $mouvement = $case->sequenceDeMouvement[$tour];
                switch ($mouvement) {
                    case MOUVEMENT::AVANCER:
                        avancerAventurier($caseList, $case);
                        break;
                    case MOUVEMENT::GAUCHE:
                        gauche($case);
                        break;
                    case MOUVEMENT::DROITE:
                        droite($case);
                        break;
                }
            }
        } else if (Cases::isGoblin($case)) {
            jouerTourGoblin($case);
        } else if (Cases::isOrc($case)) {
            jouerTourOrc($case);
        }
    }

    return array($caseList, $stillHaveSequence);
}

function jouerTourGoblin(&$case)
{
    if ($case->stepMoved == $case->stepBeforeTurn) { //s'il a atteint le nombre de mouvement avant de tourner
        //reinitialiser les steps
        $case->stepMoved = 0;
        //inverser l'orientation
        $case->shouldMoveForward = !$case->shouldMoveForward;
    }

    //bouger le goblin
    if ($case->shouldMoveForward) {
        $case->x++;
    } else {
        $case->x--;
    }

    //incrementer le nombre de mouvement
    $case->stepMoved++;

    return $case;
}

function jouerTourOrc(&$case)
{
    if ($case->stepMoved == $case->stepBeforeTurn) { //s'il a atteint le nombre de mouvement avant de tourner
        //reinitialiser les steps
        $case->stepMoved = 0;
        //inverser l'orientation
        $case->shouldMoveForward = !$case->shouldMoveForward;
    }

    //bouger l'orc
    if ($case->shouldMoveForward) {
        $case->y++;
    } else {
        $case->y--;
    }

    //incrementer le nombre de mouvement
    $case->stepMoved++;

    return $case;
}

function initList()
{
    global $width;
    global $height;
    $caseList = array();
    $fileLines = FileReader::fileToArray("map.txt");
    foreach ($fileLines as $line) {
        $infos = explode(" - ", $line);
        $type = $infos[0];

        switch ($type) {
            case CaseTypes::CASE_INFO:
                $x = (int)$infos[1];
                $y = (int)$infos[2];
                $width = $x;
                $height = $y;
                break;
            case CaseTypes::MOUNTAIN:
                $x = (int)$infos[1];
                $y = (int)$infos[2];
                $case = new Mountain($x, $y);
                array_push($caseList, $case);
                break;
            case CaseTypes::TREASURE :
                $x = (int)$infos[1];
                $y = (int)$infos[2];
                $count = (int)$infos[3];
                $case = new Treasure($x, $y, $count);
                array_push($caseList, $case);
                break;
            case  CaseTypes::AVENTURIER :
                $name = $infos[1];
                $x = (int)$infos[2];
                $y = (int)$infos[3];
                $orientation = $infos[4];
                $sequence = $infos[5];
                $case = new Adventurer($x, $y, $name, $sequence, $orientation);
                array_push($caseList, $case);
                break;
            case CaseTypes::GOBLIN :
                $x = (int)$infos[1];
                $y = (int)$infos[2];
                $level = (int)$infos[3];
                $stepBeforeTurn = (int)$infos[4];
                $case = new Goblin($x, $y, $level, $stepBeforeTurn);
                array_push($caseList, $case);
                break;
            case CaseTypes::ORC :
                $x = (int)$infos[1];
                $y = (int)$infos[2];
                $level = (int)$infos[3];
                $stepBeforeTurn = (int)$infos[4];
                $case = new Orc($x, $y, $level, $stepBeforeTurn);
                array_push($caseList, $case);
                break;
        }
    }
    return $caseList;
}