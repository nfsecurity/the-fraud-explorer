<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for managing cron jobs
 */

class CronManager
{
    protected $version;
    protected $service_executor;

    public function __construct()
    {
        $this->version = '1.0.0';
        $this->service_executor = `whoami`;
    }

    public function get_crontab(): array
    {
        exec("sudo crontab -l", $output, $exitcode);
        $result = false;

        if ($exitcode === 0) 
        {
            $result = $output;
        }
        return $result;
    }

    public function get_listed_cronjob()
    {
        exec("sudo crontab -l", $output, $exitcode);
        $result = false;

        if ($exitcode === 0) 
        {
            $result = [];
            foreach ($output as $cronjob_index => $cronjob) 
            {
                if ($cronjob && (substr($cronjob, 0, 1) != '#')) 
                {
                    $result[] = $cronjob;
                } 
                else 
                {
                    continue;
                }
            }
        }
        return $result;
    }

    public function cron_duplication_checker($cron_tag): bool
    {
        $listed_cronjob = $this->get_listed_cronjob();
        $result = false;

        if ($listed_cronjob) 
        {
            foreach ($listed_cronjob as $line => $cronjob) 
            {
                $cron_duplication_check = strpos($cronjob, '#CRONTAG='.$cron_tag);

                if ($cron_duplication_check) 
                {
                    $result = true;
                }
            }
        }
        return $result;
    }

    public function cron_get_minutes($cron_tag)
    {
        $listed_cronjob = $this->get_listed_cronjob();
        $result = false;
        $minutes = 0;

        if ($listed_cronjob) 
        {
            foreach ($listed_cronjob as $line => $cronjob) 
            {
                $cron_duplication_check = strpos($cronjob, '#CRONTAG='.$cron_tag);

                if ($cron_duplication_check) 
                {
                    $result = true;
                    $minutes = $cronjob;

                    break;
                }
            }
        }
        if ($result == true)
        {
            $arr = explode(' ', trim($minutes));
            $minutes = $arr[0];
            $arr = explode('/', trim($minutes));
            $minutes = $arr[1];

            return $minutes;
        }
        else return "false";
    }

    public function cron_get_portion($cron_tag, $portion)
    {
        $listed_cronjob = $this->get_listed_cronjob();
        $result = false;
        $segment = 0;
        $minutes = 0;
        $hours = 0;
        $day = 0;
        $month = 0 ;
        $weekday = 0;

        if ($listed_cronjob) 
        {
            foreach ($listed_cronjob as $line => $cronjob) 
            {
                $cron_duplication_check = strpos($cronjob, '#CRONTAG='.$cron_tag);

                if ($cron_duplication_check) 
                {
                    $result = true;
                    $segment = $cronjob;

                    break;
                }
            }
        }
        if ($result == true)
        {
            $arr = explode(' ', trim($segment));
            $minutes = $arr[0];
            $hours = $arr[1];
            $day = $arr[2];
            $month = $arr[3];
            $weekday = $arr[4];

            switch($portion)
            {
                case "minutes" : return $minutes;
                    break;
                case "hours" : return $hours;
                    break;
                case "day" : return $day;
                    break;
                case "month" : return $month;
                    break;
                case "weekday" : return $weekday;
                    break;
                default: return "false";
            }
        }
        else return "false";
    }

    public function add_cronjob($command, $cron_tag): array
    {
        $result = array(
            'status' => 'status',
            'msg'    => 'msg',
            'data'   => 'data'
        );

        $cron_duplication_check = $this->cron_duplication_checker($cron_tag);
        $managed_command = '(sudo crontab -l; echo "'.$command.' #CRONTAG='.$cron_tag.'") | sudo crontab -';

        if (!$cron_tag) 
        {
            $result['status'] = 'INPUT_ERROR';
            $result['msg'] = 'cron_tag is required';
            $result['data'] = $managed_command;
        } 
        else if ($cron_duplication_check) 
        {
            $result['status'] = 'FAILED';
            $result['msg'] = 'duplicated cron tag exists';
            $result['data'] = $cron_duplication_check;
        } 
        else 
        {
            exec($managed_command, $output, $exitcode);
            $result['data'] = array(
                'cron_add_output'   => $output,
                'cron_add_exitcode' => $exitcode,
                'managed_command'   => $managed_command
            );

            if ($exitcode === 0) 
            {
                $result['status'] = 'SUCCESS';
                $result['msg'] = 'added new cronjob';
            } 
            else if ($exitcode === 127) 
            {
                $result['status'] = 'ERROR';
                $result['msg'] = 'crond is not running or not installed';
            } 
            else 
            {
                $result['status'] = 'ERROR';
                $result['msg'] = 'error occurred in progress to register new cron job';
            }
        }

        return $result;
    }

    public function remove_cronjob($cron_tag): bool
    {
        $cron_duplication_check = $this->cron_duplication_checker($cron_tag);
        $result = false;
        
        if ($cron_duplication_check) 
        {
            exec("sudo crontab -l | sed '/\(.*#CRONTAG=$cron_tag\)/d' | sudo crontab ", $output, $exit_code);
            
            if ($exit_code === 0) 
            {
                $result = true;
            }
        }
        
        return $result;
    }
}
