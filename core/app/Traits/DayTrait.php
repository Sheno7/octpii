<?php
namespace App\Traits;

trait DayTrait
{
    public function getCurrentDay()
    {
        return now()->format('l');
    }

    public function getDayId($day)
    {
        $day = strtolower($day);
        $days = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6];
        return $days[$day];
    }

    public function getDayNamefromId($day)
    {
        $days = ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday',
            '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'];
        return $days[$day];
    }


    public function get_date_for_day($day)
    {
        $day = $this->getDayNamefromId($day);
        return now()->addDays($day)->format('Y-m-d');
    }

    public function get_next_dates_for_days($day = null, $monthCount = 1)
    {
        $dates = [];
        if ($day !== null) {
            $dayname = $this->getDayNamefromId($day);
            $currentDayOfWeek = strtolower(now()->format('l'));
            $dayOfWeek = strtolower($dayname);
            for ($i = 0; $i <= 5; $i++)
            {
                $daysUntilNext = ($this->getDayId($dayOfWeek) - $this->getDayId($currentDayOfWeek) + 7) % 7;
                $dates[] = now()->addDays($daysUntilNext + ($i * 7))->format('Y-m-d');
            }
        }
        return $dates;
    }

    public function week_days()
    {
        return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    }
}
