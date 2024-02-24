<?php

namespace App\Topnode\BaseBundle\Utils\Date;

class Period
{
    public \DateTimeInterface $start;
    public \DateTimeInterface $end;

    public function __construct(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        if ($start > $end) {
            $this->start = $end;
            $this->end = $start;
        } else {
            $this->start = $start;
            $this->end = $end;
        }
    }

    public function completeDay()
    {
        $this->start->setTime(0, 0, 0);
        $this->end->setTime(23, 59, 59);

        return $this;
    }

    public function diffDays()
    {
        return $this->start->diff($this->end)->days;
    }
}
