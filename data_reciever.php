<?php

require_once 'db.connect.inc.php';

class data_reviever
{

    public $title = ' ';
    public $text = ' ';
    private $statuses = array
    (
        "Рабочий"
    ,   "Отпуск"
    ,   "Отпросился"
    ,   "Заболел"
    ,   "Отработал"
    ,   "Работал удаленно"
    );

    public $months_array = array
    (
        1=>'Январь'
    ,   2=>'Февраль'
    ,   3=>'Март'
    ,   4=>'Апрель'
    ,   5=>'Май'
    ,   6=>'Июнь'
    ,   7=>'Июль'
    ,   8=>'Август'
    ,   9=>'Сентябрь'
    ,   10=>'Октябрь'
    ,   11=>'Ноябрь'
    ,   12=>'Декабрь'
    );
    function __construct()
    {

        $month = $this->months_array[intval(date('m'))];
        $this->title = "Уведомление о пропусках за ".$month. ' '.date('Y');
        $this->text = $this->showTablesForAllUsers();

    }


    private function showTablesForAllUsers()
    {
        $con = mysql_pconnect(MysqlHost,MysqlUser,MysqlPWD);
        mysql_query( "USE shanyuk_report;",$con) or die("не удалось подключиться к базе");


        //Fetching all the rows as objects
        $users = $this->get_users($con);

        $res = '<h1>'.$this->title.'</h1>';
        foreach ($users as $user)
            $res .= $this->get_skips($con, $user);

        //Closing the connection
        mysql_close($con);

        return $res;

    }

    private function get_users($con)
    {
        $res = mysql_query( "SELECT * FROM main__users",$con);

        //Fetching all the rows as objects
        $users = array();
        while($obj = mysql_fetch_object($res))
        {
            if($obj->id!=1)
//        {
//            print_r($obj);
//            echo "<br>\n";
                array_push($users, $obj);
//        }

        }
        //Closing the statement
        mysql_free_result($res);
        return $users;
    }
    private function get_skips($con, $user)
    {
        $id = $user->id;

        $month = date('m');
        $year = date('Y');

        $QS = "SELECT   main__skipped.`status`,main__skipped.`date_skipped_start`
                        ,main__skipped.`fulfilled_day`, main__skipped.`fullfilled_hours` 
                FROM main__skipped 
                LEFT JOIN  main__users ON main__skipped.user_id =  main__users.id 
                WHERE main__users.`id`= {$id} 
                
                AND MONTH(main__skipped.`date_skipped_start`) = {$month} 
                AND YEAR(main__skipped.`date_skipped_start`) = {$year}  ";
        $res = mysql_query($QS,$con);
//
//    print_r($user);
//    echo "<br>\n";

        $absences = array();
        while($obj = mysql_fetch_object($res))
//    {
//        print_r($obj);
//        echo "<br>\n";
            array_push($absences, $obj);
//    }
        //Closing the statement
        mysql_free_result($res);

        $name = ($user->Description)?$user->Description:$user->UserName;

        $res = $this->fill_user_table($absences, $name);

        return $res;
    }

    private function fill_user_table($baseObj,$username)
    {
        $statuses_values = array_fill(0,count( $this->statuses),0);
        foreach ($baseObj AS $info)
        {
            if($info->status!=4)
                ++$statuses_values[$info->status];
            else
                $statuses_values[$info->status]+=$info->fullfilled_hours;
        }


        $res = ' <table   > <caption>'.$username.'</caption>';
        for ($i=1;$i<count($statuses_values);$i++) //статус 0 - не пропуск и мы его не отображаем
            $res .=  $this->infoUserTasksDatee($i, $statuses_values[$i]);
        $res .= '</table>';

        return $res;
    }

    private function infoUserTasksDatee($status, $value)
    {


        if($status==4)
            $value = $this->view_hours($value);
        else $value =  $this->view_days($value);

        GLOBAL $statuses;
        $res = '<tr style="color: #3e3d3d"><td>'.$statuses[$status].'</td><td>'.$value.'</td></tr>';

        return $res;

    }

    private function view_hours($hours)
    {
        $view_hours = $hours.' час';
        $hours_as_string = (string)$hours;

        $last_number =  intval($hours_as_string[strlen($hours_as_string)-1]);

        if($last_number!=1)
            $view_hours .= ($last_number<5&&$last_number>1)? 'а':"ов";


        return $view_hours;
    }

    private function view_days($days)
    {
        $view_days = $days.' ';
        $days_as_string = (string)$days;

        $last_number =  intval($days_as_string[strlen($days_as_string)-1]);

        switch($last_number)
        {
            case 1:
                $view_days.='день';
                break;
            case 2:
            case 3:
            case 4:
                $view_days.='дня';
                break;
            default:
                $view_days.='дней';
                break;
        }

        return $view_days;
    }







}


?>