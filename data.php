<?php


abstract class CaseTypes
{
    const MOUNTAIN = "M";
    const PLAIN = ".";
    const TREASURE = 'T';
    const CASE_INFO = 'C';
    const AVENTURIER = 'A';
    const GOBLIN = 'G';
    const ORC = 'O';

}

abstract class ORIENTATION
{
    const ORIENTATION_NORD = 'N';
    const ORIENTATION_SUD = 'S';
    const ORIENTATION_EST = 'E';
    const ORIENTATION_OUEST = 'O';
}

abstract class MOUVEMENT
{
    const GAUCHE = "G";
    const DROITE = "D";
    const AVANCER = "A";

}

abstract class Cases
{
    public $x = 0;
    public $y = 0;

    function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    static function isTreasure($case)
    {
        return is_a($case, 'Treasure');
    }

    static function isAdventurer($case)
    {
        return is_a($case, 'Adventurer');
    }

    static function isMountain($case)
    {
        return is_a($case, 'Mountain');
    }

    static function isOrc($case)
    {
        return is_a($case, 'Orc');
    }

    static function isGoblin($case)
    {
        return is_a($case, 'Goblin');
    }

    static function printPlain()
    {
        echo "   " . CaseTypes::PLAIN . "      ";
    }

    abstract function print();

    abstract function writeResult($filePath);
}

class Mountain extends Cases
{
    function print()
    {
        echo "   " . CaseTypes::MOUNTAIN . "      ";
    }

    function writeResult($filePath)
    {
        $separateur = " - ";
        fwrite($filePath,CaseTypes::MOUNTAIN . $separateur . $this->x .$separateur. $this->y );
    }
}

class Treasure extends Cases
{
    public $treasureCount = 0;


    function __construct($x, $y, $treasureCount)
    {
        parent::__construct($x, $y);
        $this->treasureCount = $treasureCount;
    }

    function print()
    {
        if($this->treasureCount > 0){
            echo "  " . CaseTypes::TREASURE . "(" . $this->treasureCount . ")    ";
        }

    }


    function writeResult($filePath)
    {
        $separateur = " - ";
        fwrite($filePath,CaseTypes::TREASURE .$separateur. $this->x .$separateur. $this->y .$separateur. $this->treasureCount );
    }
}

class Adventurer extends Cases
{
    public $nomAdventurer = "";
    public $level = 0;
    public $sequenceDeMouvement = "";
    public $orientation = "";
    public $isDead = false;

    private function formatName()
    {
        return substr($this->nomAdventurer, 0, 3);
    }

    function print()
    {
        if (!$this->isDead) {
            echo "   " . CaseTypes::AVENTURIER . "(" . $this->formatName() . ") ";
        }
    }

    function __construct($x, $y, $nomAdventurer, $sequence, $orientation)
    {
        parent::__construct($x, $y);
        $this->nomAdventurer = $nomAdventurer;
        $this->sequenceDeMouvement = trim($sequence);
        $this->orientation = $orientation;
    }

    function writeResult($filePath)
    {
        $separateur = " - ";
        fwrite($filePath,CaseTypes::AVENTURIER.$separateur. $this->nomAdventurer .$separateur. $this->x .$separateur. $this->y .$separateur. $this->orientation.$separateur.$this->level );
    }
}

class Goblin extends Cases
{
    public $level = 0;
    public $stepBeforeTurn = 0;
    public $stepMoved = 0;
    public $shouldMoveForward = true;
    public $isDead = false;

    function __construct($x, $y, $level, $stepBeforeTurn)
    {
        parent::__construct($x, $y);
        $this->level = $level;
        $this->stepBeforeTurn = $stepBeforeTurn;
    }

    function print()
    {
        if (!$this->isDead) {
            echo "   " . CaseTypes::GOBLIN . "      ";
        }
    }

    function isdead(){
        if (!$this->isDead){
            $state = "L";
        }
        else {
            $state = "D";
        }
        return $state ;
    }

    function writeResult($filePath)
    {
        $separateur = " - ";
        fwrite($filePath,CaseTypes::GOBLIN.$separateur. $this->x .$separateur. $this->y .$separateur. $this->isdead().$separateur );
    }
}

class Orc extends Cases
{
    public $level = 0;
    public $stepBeforeTurn = 0;
    public $stepMoved = 0;
    public $shouldMoveForward = true;
    public $isDead = false;

    function __construct($x, $y, $level, $stepBeforeTurn)
    {
        parent::__construct($x, $y);
        $this->level = $level;
        $this->stepBeforeTurn = $stepBeforeTurn;
    }

    function print()
    {
        if (!$this->isDead) {
            echo "   " . CaseTypes::ORC . "      ";
        }
    }

    function isdead(){
        if (!$this->isDead){
            $state = "D";
        }
        else {
            $state = "L";
        }
        return $state ;
    }

    function writeResult($filePath)
    {
        $separateur = " - ";
        fwrite($filePath,CaseTypes::GOBLIN.$separateur. $this->x .$separateur. $this->y .$separateur. $this->isdead().$separateur.$this->level );
    }
}