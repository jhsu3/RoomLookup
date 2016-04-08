<?php
class App_Apiendpoint_RoomLookup extends Ot_Api_EndpointTemplate
{
    /**
     * Shows details about classes being taught in a room
     *
     * Params
     * ===========================
     * Required
     *   - Building
     *   - Room number
     *   - Day
     */

    //App_Model_DbTable_Course->getCoursesByRoomsAndDate
    public function get($params)
    {
        if (!isset($params['buildingAbbreviation'])) {
            throw new Ot_Exception_ApiMissingParams('Missing prefix parameter');
        }
        if (!isset($params['roomNumber'])) {
            throw new Ot_Exception_ApiMissingParams('Missing number parameter');
        }
        }
        if (!isset($params['day'])) {
            throw new Ot_Exception_ApiMissingParams('Missing number parameter');
        }
        $buildingAbbreviation = $params['buildingAbbreviation'];
        $roomNumber = $params['roomNumber'];
        $day = $params['day'];
        //Building abbreviation -> building ID
        $buildingTable = new App_Model_DbTable_Building();
        $buildingId = $buildingTable->getBuildingByAbbreviation($buildingAbbreviation);
        $buildingName = $buildingTable->find($buildingId)['name'];          //Get name of building from buildingId

        $roomTable = new App_Model_dbTable_Room();
        $buildingRooms = $roomTable->getRoomsByBuildingIds($buildingId);    //Get all roomIds in buildingId
        foreach($buildingRooms as $search)
        {
            if($roomNumber == $roomTable->find($search)) $roomId = $search;  //Match desired room number to get roomId
            //TODO add error catching here
        }

        $courseTable = new App_Model_DbTable_Course();
        $instructorTable = new App_Model_DbTable_CourseInstructor();
        $courses = $courseTable->getCoursesByRoomsAndDate($roomId, $day);   //Array of courses in room on day?  //Can also use getCourses?
        $days = array(
            'Mon' => 'monday',
            'Tue' => 'tuesday',
            'Wed' => 'wednesday',
            'Thu' => 'thursday',
            'Fri' => 'friday',
            'Sat' => 'saturday',
            'Sun' => 'sunday',
        );
        foreach($courses as $c)
        {
            foreach($days as $d)
            {
                if($c[$d] == 1) array_push($daysTaught, $d);    //Add days to array
            }
            $courseDetails = array(
                            'buildingName' => $buildingName,
                            'roomNumber' => $roomNumber,
                            'className' => $c['name'],
                            'daysTaught' => $daysTaught,
                            'timeTaught' => $c['startTime'],
                            'instructorInfo' => instructorTable->find($c);
            );
            array_push($result, $courseDetails);
        }
        return $result;
    }
}