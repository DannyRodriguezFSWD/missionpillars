<?php

namespace App\Classes\IcsExport;

class IcsExport {
    private $data = "";
    private $name;
    private $start = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\n";
    private $end = "END:VCALENDAR\n";

    public function __construct($name)
    {
        $this->name = $name;
        $name = substr($name,'3',strlen($name));
        $this->start .= "X-WR-CALNAME:$name\nNAME:$name\n";
    }

    function add($start, $end, $name, $description, $location, $is_all_day, $uid, $freq = null) {
        if ($freq) {
            $freq = [
                'repeat_cycle' => "\nRRULE:FREQ=".strtoupper($freq['repeat_cycle']),
                'repeat_every' => $freq['repeat_every'] ? "INTERVAL=".$freq['repeat_every'] : null,
                'repeat_occurrences' => $freq['repeat_occurrences'] ? "COUNT=".$freq['repeat_occurrences'] : null,
                'repeat_ends_on' => $freq['repeat_ends_on'] ? "UNTIL=".date("Ymd\THis\Z",strtotime($freq['repeat_ends_on'])): null,
            ];

            $freq = join(';',array_filter($freq));
        }

        if ($is_all_day) {
            $startDate = "\nDTSTART:" . date("Ymd", strtotime($start));
            $endDate = null;
        } else {
            $startDate = "\nDTSTART:" . date("Ymd\THis\Z", strtotime($start));
            $endDate = "\nDTEND:" . date("Ymd\THis\Z", strtotime($end));
        }

        $this->data .= "BEGIN:VEVENT"
            .$startDate
            .$endDate
            //."$freq"          //REMOVING RRULE FOR REPEATING EVENT FOR NOW
            ."\nLOCATION:".$location."\nTRANSP: OPAQUE\nSEQUENCE:0\nUID:$uid\nDTSTAMP:".date("Ymd\THis\Z")."\nSUMMARY:".$name."\nDESCRIPTION:".$description."\nPRIORITY:1\nCLASS:PUBLIC\nBEGIN:VALARM\nTRIGGER:-PT10080M\nACTION:DISPLAY\nDESCRIPTION:Reminder\nEND:VALARM\nEND:VEVENT\n";
    }
    function download() {
        $header = [
            "Content-type" => "text/calendar",
            "Content-Disposition" => "attachment; filename=".$this->name.".ics",
            "Content-Length" => strlen($this->getData()),
        ];
        return response($this->getData(),200,$header);
    }
    function getData() {
        return $this->start . $this->data . $this->end;
    }
}
