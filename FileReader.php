<?php

class FileReader
{

    static function exportToFile(array $caseList)
    {
        $file = fopen("game.txt", "w") or die ("unable to open file");
        if ($file) {
            foreach ($caseList as $case) {
                $case->writeResult($file);
                fwrite($file,"\n");
            }
            fclose($file);
        }
        return $file;
    }

    static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }


    static function fileToArray($filePath)
    {
        $array = array();
        foreach (file($filePath) as $line) {
            //ignore lines that starts with #
            if (!FileReader::startsWith($line, "#")) {
                array_push($array, $line);
            }
        }

        return $array;
    }
}

