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
                if($c[$d] == 1)
            }
            $courseDetails = array(
                            'buildingName' => $buildingName,
                            'roomNumber' => $roomNumber,
                            'className' => $c['name'],
                            'daysTaught'
            );
        }


        $courseDba = $courseTable->getAdapter();
        // Select only what we need from the start so we don't have to clean up later
        $select = $courseDba->select()
                        ->from($courseTable->_name, array(
                                'courseId',
                                'prefix_long',
                                'prefix',
                                'number',
                                'section',
                                'name',
                                'semesterId',
                                'startTime',
                                'endTime',
                                'longDescription',
                                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'))
                        ->where($courseDba->quoteInto('prefix = ?', $coursePrefix) . ' AND ' . $courseDba->quoteInto('number = ?', $courseNumber))
                        ->order(array('section'));
        $result = $courseDba->fetchAll($select);
        $topCourse = $result[0];
        $course = array(
            'prefix' => $topCourse['prefix'],
            'prefix_name' => $topCourse['prefix_long'],
            'number' => $topCourse['number'],
            'name' => $topCourse['name'],
            'description' => $topCourse['longDescription'],
            'sections' => array()
        );
        $semesterTable = new App_Model_DbTable_Semester();
        $semester = $semesterTable->find($topCourse['semesterId']);
        $course['semester'] = $semester['name'];
        foreach ($result as $c) {
            $section = array(
                    'courseId' => $c['courseId'],
                    'section' => $c['section'],
                    'startTime' => $c['startTime'],
                    'endTime' => $c['endTime'],
            );
            $days = array_splice($c, 11, 7, null);
            $section['days'] = Internal_Format_CourseDays::format($days);
            $course['sections'][] = $section;
        }
        return $course;
    }
}