<?php
/******************************************************************************
    Interface definition for Command design pattern
    
    @copyright  Thierry Graff
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    
    @history    2019-05-11 17:33:48+02:00, Thierry Graff : Creation
    @history    2021-09-16 12:26:02+02:00, Thierry Graff : Integrate to tiglib
********************************************************************************/
namespace tiglib\patterns\command;

interface Command {
    
    /** 
        Do something
    **/
    public static function execute($params=[]);
    
} // end interface
