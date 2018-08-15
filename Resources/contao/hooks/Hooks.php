<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 10.01.2018
 * Time: 16:48
 */

namespace Home\PearlsBundle\Resources\contao\hooks;


class Hooks
{


    public function cron()
    {
        $this->backupCron();
    }

    /**
     * will take a backup from DB on initialize after X hours
     * deletes old backups when a specific backup count is reached
     */
    private function backupCron()
    {
        #-- path to dir and file
        $rootDir = \System::getContainer()->getParameter('kernel.project_dir');
        $filesDir = '/files/dbBackup';
        $file = $rootDir . $filesDir . '/lastBackup.txt';
        #-- after how many hours the backup will executed again
        $hours = 24;
        #-- how many backup files will be stored
        $stored = 30;

        #-- check if dir exists
        if(!is_dir($rootDir . $filesDir)){
            mkdir($rootDir . $filesDir);
        }

        #-- check of file exists
        if(!file_get_contents($file)){
            #-- no file or empty take backup now
            $time = self::backupNow();
        }else{
            #-- check if last backup was X hours ago
            $content = file_get_contents($file);

            if($content <= strtotime('-' . $hours . ' hours')){
                $time = self::backupNow();
            }
        }

        #-- write time in file for next check
        if($time){
            $handle = fopen($file, 'w+');
            fwrite($handle, $time);
            fclose($handle);
        }
    }

    /**
     * take backup via mysqldump
     *
     * @return int
     */
    public static function backupNow()
    {
        #-- db connection info
        $dbHost = \Config::get('dbHost');
        $dbPort = \Config::get('dbPort');
        $dbUsername = \Config::get('dbUser');
        $dbPassword = \Config::get('dbPass');
        $dbName = \Config::get('dbDatabase');
        #-- path to dir and file
        $rootDir = \System::getContainer()->getParameter('kernel.project_dir');
        $filesDir = '/files/dbBackup';
        $file = $rootDir . $filesDir . '/'.$dbName.'_Dump'.date("Ymd_His").'.sql';

        #-- check if dir exists
        if(!is_dir($rootDir . $filesDir)){
            mkdir($rootDir . $filesDir);
        }

        #-- execute mysqldump command
        exec("mysqldump --opt --default-character-set=UTF8 --single-transaction --protocol=TCP --user=".$dbUsername." --password=".$dbPassword." --host=".$dbHost." ".$dbName." > ".$file);

        return time();
    }
}