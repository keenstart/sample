<?php

namespace Application\Models\XMLTeam;

class ProcessDateTime{
  
  public static function processTime($time, $returnObject=false){
    $parsedTime = self::parseTime($time);
    $dateTime = new \DateTime($parsedTime['time'], new \DateTimeZone('UTC'));
    $getOffset = $parsedTime['offset']/100;
    
    $invert = true;
    
    if(abs($getOffset) !== $getOffset){
      $invert = false;
    }
    
    $getOffset = abs($getOffset);
    
    if(floor($getOffset) == $getOffset){
      $offsetInSeconds = $getOffset * 3600;
    } else{
      $partialHours = $getOffset - floor($getOffset);
      $partialHours = $partialHours*100;
      $partialHours = $partialHours/60;
      $offsetInSeconds = floor($getOffset) + $partialHours;
      $offsetInSeconds = $offsetInSeconds * 3600;
    }
    
    $offsetInterval = new \DateInterval("PT" . $offsetInSeconds . "S");
    if($invert){
      $offsetInterval->invert = 1;
    }
    
    $dateTime->add($offsetInterval);
    if($returnObject){
      return $dateTime;
    } else{
      return $dateTime->format('Y-m-d H:i:s');
    }
  }
  
  protected static function parseTime($timeString){
    $time = explode('T', $timeString);
  
    $date = $time[0];
    $eventTime = $time[1];
  
    $year = substr($date, 0, 4);
    $month = substr($date, 4, 2);
    $day = substr($date, 6, 2);
  
    $dateString = $year . '-' . $month . '-' . $day;
  
    $dateString .= ' ' . substr($eventTime, 0, 2) . ':' . substr($eventTime, 2, 2) . ':' . substr($eventTime, 4, 2);
  
    $timeOffset = substr($eventTime, 6);
  
    return Array('time' => $dateString, 'offset' => $timeOffset);
  }
  
}