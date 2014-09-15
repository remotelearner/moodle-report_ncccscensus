<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * NCCCS Census report tests
 *
 * @package   report_ncccscensus
 * @category  test
 * @copyright 2014 Remote-Learner Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_ncccscensus_testcase extends advanced_testcase {
    /**
     * Basic test of no data passed.
     *
     * @return void
     */
    public function test_nodata() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $this->resetAfterTest(true);
        $formdata = new stdClass;
        $this->assertFalse(ncccscensus_get_courses($formdata));
    }

    /**
     * Test of only category selected.
     *
     * @return void
     */
    public function test_categoryonly() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();

        // Test parent category selection.
        $formdata = new stdClass;
        $formdata->categories = $data['category1']->id;
        $courses = array($data['course1']->id);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Test subcategory selection.
        $formdata->categories = $data['category2']->id;
        $courses = array($data['course2']->id);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Test subcategory selection with two courses in it.
        $formdata->categories = $data['category3']->id;
        $courses = array($data['course3']->id, $data['course4']->id, $data['course5']->id);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Test two category selections with three courses in it.
        $formdata->categories = join(',', array($data['category2']->id, $data['category3']->id));
        $courses = array($data['course2']->id, $data['course3']->id, $data['course4']->id, $data['course5']->id);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);
    }

    /**
     * Test of only courses selected.
     *
     * @return void
     */
    public function test_coursesonly() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();

        $formdata = new stdClass;
        $courses = array($data['course1']->id);
        $formdata->courses = join(',', $courses);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        $courses = array($data['course1']->id, $data['course2']->id);
        $formdata->courses = join(',', $courses);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        $courses = array($data['course1']->id, $data['course2']->id, $data['course3']->id);
        $formdata->courses = join(',', $courses);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Setting categories with courses selected should not change result.
        // Selected courses are assumed to be from selected categories.
        $formdata->categories = $data['category2']->id;

        $courses = array($data['course1']->id);
        $formdata->courses = join(',', $courses);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        $courses = array($data['course1']->id, $data['course2']->id);
        $formdata->courses = join(',', $courses);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        $courses = array($data['course1']->id, $data['course2']->id, $data['course3']->id);
        $formdata->courses = join(',', $courses);
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);
    }

    /**
     * Test of only teachers selected.
     *
     * @return void
     */
    public function test_teachersonly() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();
        $formdata = new stdClass;

        // Teacher with two courses.
        $courses = array($data['course1']->id, $data['course2']->id);
        $formdata->teachers = join(',', array($data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Teacher with one course.
        $courses = array($data['course3']->id);
        $formdata->teachers = join(',', array($data['user2']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Two teachers selected.
        $courses = array($data['course1']->id, $data['course2']->id, $data['course3']->id);
        $formdata->teachers = join(',', array($data['user2']->id, $data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);
    }


    /**
     * Test of teachers and categories selected.
     *
     * @return void
     */
    public function test_teacherscategories() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();
        $formdata = new stdClass;

        // Teacher with two courses and one exists in category 1.
        $courses = array($data['course1']->id);
        $formdata->teachers = join(',', array($data['user1']->id));
        $formdata->categories = $data['category1']->id;
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Teacher with two courses and one exists in category 2.
        $courses = array($data['course2']->id);
        $formdata->teachers = join(',', array($data['user1']->id));
        $formdata->categories = $data['category2']->id;
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Teacher with two courses and none exists in category.
        $courses = false;
        $formdata->teachers = join(',', array($data['user1']->id));
        $formdata->categories = $data['category4']->id;
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Teacher and category 1.
        $courses = array($data['course3']->id);
        $formdata->teachers = join(',', array($data['user2']->id));
        $formdata->categories = $data['category3']->id;
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Two teachers selected and category 3.
        $courses = array($data['course3']->id);
        $formdata->categories = $data['category3']->id;
        $formdata->teachers = join(',', array($data['user2']->id, $data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Two teachers selected, category 1 and category 3.
        $courses = array($data['course1']->id, $data['course3']->id);
        $formdata->categories = join(',', array($data['category1']->id, $data['category3']->id));
        $formdata->teachers = join(',', array($data['user2']->id, $data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Two teachers selected and category 4.
        $courses = array($data['course1']->id, $data['course3']->id);
        $formdata->categories = join(',', array($data['category4']->id));
        $formdata->teachers = join(',', array($data['user2']->id, $data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), false);

    }

    /**
     * Test of courses and teachers selected.
     *
     * @return void
     */
    public function test_coursesteachers() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();
        $formdata = new stdClass;

        // Teacher with two courses and only show one.
        $courses = array($data['course1']->id);
        $formdata->courses = $data['course1']->id;
        $formdata->teachers = join(',', array($data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Two teacher with two courses and only show one.
        $courses = array($data['course1']->id);
        $formdata->courses = $data['course1']->id;
        $formdata->teachers = join(',', array($data['user1']->id, $data['user2']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Teacher with two courses and show none as teacher is not enrolled.
        $courses = false;
        $formdata->courses = $data['course4']->id;
        $formdata->teachers = join(',', array($data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Setting categories with courses selected should not change result.
        // Selected courses are assumed to be from selected categories.
        $formdata->categories = $data['category2']->id;

        // Teacher with two courses and only show one.
        $courses = array($data['course1']->id);
        $formdata->courses = $data['course1']->id;
        $formdata->teachers = join(',', array($data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Two teacher with two courses and only show one.
        $courses = array($data['course1']->id);
        $formdata->courses = $data['course1']->id;
        $formdata->teachers = join(',', array($data['user1']->id, $data['user2']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);

        // Teacher with two courses and show none as teacher is not enrolled.
        $courses = false;
        $formdata->courses = $data['course4']->id;
        $formdata->teachers = join(',', array($data['user1']->id));
        $this->assertEquals(ncccscensus_get_courses($formdata), $courses);
    }

    /**
     * Test of courses query.
     *
     * @return void
     */
    public function test_coursesquery() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();
        $results = report_ncccscensus_course_search('noresults', array());
        $this->assertEquals(count($results), 1);
        $this->assertEquals($results[0]['name'], "No matches found for: noresults");
        $results = report_ncccscensus_course_search('course', array());
        $this->assertEquals(4, count($results));
        $results = report_ncccscensus_course_search('course', array(array('id' => $data['category3']->id)));
        $this->assertEquals(2, count($results));
        $courses = array();
        foreach ($results as $course) {
            array_push($courses, $course['name']);
        }
        $this->assertTrue(in_array('Test course 4', $courses));
    }

    /**
     * Test of category query.
     *
     * @return void
     */
    public function test_categoryquery() {
        global $CFG;
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata();
        $results = report_ncccscensus_category_search('noresults', array());
        $this->assertEquals(count($results), 1);
        $results = report_ncccscensus_category_search('Business', array());
        $this->assertEquals(count($results), 1);
        $categories = array();
        foreach ($results as $category) {
            array_push($categories, $category['name']);
        }
        $this->assertTrue(in_array('Summer / Business', $categories));
    }

    /**
     * Create test data.
     *
     * @return void
     */
    private function createdata() {
        $this->resetAfterTest(true);
        $data = array();

        // Create some users.
        $data['user1'] = $this->getDataGenerator()->create_user(array('email' => 'user1@example.com', 'username' => 'testuser1'));
        $data['user2'] = $this->getDataGenerator()->create_user(array('email' => 'user2@example.com', 'username' => 'testuser2'));
        $data['user3'] = $this->getDataGenerator()->create_user(array('email' => 'user3@example.com', 'username' => 'testuser3'));
        $data['user4'] = $this->getDataGenerator()->create_user(array('email' => 'user4@example.com', 'username' => 'testuser4'));

        // Create some categories.
        $category = array('name' => 'Summer');
        $data['category1'] = $this->getDataGenerator()->create_category($category);
        $category = array('name' => 'Business', 'parent' => $data['category1']->id);
        $data['category2'] = $this->getDataGenerator()->create_category($category);
        $category = array('name' => 'Health', 'parent' => $data['category1']->id);
        $data['category3'] = $this->getDataGenerator()->create_category($category);
        $category = array('name' => 'Office', 'parent' => $data['category1']->id);
        $data['category4'] = $this->getDataGenerator()->create_category($category);

        // Create some courses.
        $course = array('name' => 'Course 1', 'category' => $data['category1']->id);
        $data['course1'] = $this->getDataGenerator()->create_course($course);
        $course = array('name' => 'Course 2', 'category' => $data['category2']->id);
        $data['course2'] = $this->getDataGenerator()->create_course($course);
        $course = array('name' => 'Course 3', 'category' => $data['category3']->id);
        $data['course3'] = $this->getDataGenerator()->create_course($course);
        $course = array('name' => 'Course 4', 'category' => $data['category3']->id);
        $data['course4'] = $this->getDataGenerator()->create_course($course);
        // This course should have no enroled students and not show up in course searches.
        $course = array('name' => 'Course 5', 'category' => $data['category3']->id);
        $data['course5'] = $this->getDataGenerator()->create_course($course);

        // Enrol some teachers.
        $this->getDataGenerator()->enrol_user($data['user1']->id, $data['course1']->id, 4);
        $this->getDataGenerator()->enrol_user($data['user1']->id, $data['course2']->id, 4);
        $this->getDataGenerator()->enrol_user($data['user2']->id, $data['course3']->id, 4);

        // Enrol some students, these should not be seen by tests.
        $this->getDataGenerator()->enrol_user($data['user3']->id, $data['course1']->id);
        $this->getDataGenerator()->enrol_user($data['user3']->id, $data['course2']->id);
        $this->getDataGenerator()->enrol_user($data['user4']->id, $data['course1']->id);
        $this->getDataGenerator()->enrol_user($data['user4']->id, $data['course4']->id);
        return $data;
    }

    /**
     * This function creates setup data for teacher auto complete filter.
     * @return array An array of setup data.
     */
    private function createdata_for_teacherfilter() {
        // Create a teacher user.
        $data['user1'] = $this->getDataGenerator()->create_user(array('email' => 'teacher1@example.com', 'username' => 'testteacher1'));
        $data['user2'] = $this->getDataGenerator()->create_user(array('email' => 'teacher2@example.com', 'username' => 'testteacher2'));

        // Create a 2 course categories.
        $category = array('name' => 'Summer');
        $data['category1'] = $this->getDataGenerator()->create_category($category);
        $category = array('name' => 'Winter');
        $data['category2'] = $this->getDataGenerator()->create_category($category);
        // Add 2 courses to the 'Summer' category.
        $data['course1'] = $this->getDataGenerator()->create_course(array('name' => 'Course 1', 'category' => $data['category1']->id));
        $data['course2'] = $this->getDataGenerator()->create_course(array('name' => 'Course 2', 'category' => $data['category1']->id));
        // Add 3 courses to the 'Winter' category.
        $data['course3'] = $this->getDataGenerator()->create_course(array('name' => 'Course 3', 'category' => $data['category2']->id));
        $data['course4'] = $this->getDataGenerator()->create_course(array('name' => 'Course 4', 'category' => $data['category2']->id));
        $data['course5'] = $this->getDataGenerator()->create_course(array('name' => 'Course 5', 'category' => $data['category2']->id));

        return $data;
    }

    /**
     * This function tests the teacher searching when only a course is selected.
     */
    public function test_teachersquery_filter_by_course() {
        global $CFG;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata_for_teacherfilter();

        // Get a roles that have the capability.
        $coursecontext = context_course::instance($data['course1']->id);
        $roles = get_role_names_with_caps_in_context($coursecontext, array('moodle/grade:edit'));

        // Assign role and remove capability.  Test to make sure no user is returned.
        $roleid = 0;
        foreach ($roles as $rid => $role) {
            $roleid = $rid;
            $this->getDataGenerator()->enrol_user($data['user1']->id, $data['course1']->id, $roleid);
            role_change_permission($roleid, $coursecontext, 'moodle/grade:edit', CAP_PREVENT);
            break;
        }

        $results = report_ncccscensus_teacher_search('teacher', array(array('id' => $data['course1']->id)));
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayNotHasKey('id', $results[0]);

        // Set role capability to allow.  Test the make sure a user is returned.
        role_change_permission($roleid, $coursecontext, 'moodle/grade:edit', CAP_ALLOW);

        $results = report_ncccscensus_teacher_search('teacher', array(array('id' => $data['course1']->id)));
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        // Test to make sure no user is returned, by using a search text that doesn't exist in the user's first, last and username.
        $results = report_ncccscensus_teacher_search('doesnotexist', array(array('id' => $data['course1']->id)));
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayNotHasKey('id', $results[0]);
    }

    /**
     * This function tests the teacher searching when a course category is selected.
     */
    public function test_teachersquery_filter_by_coursecategory() {
        global $CFG;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata_for_teacherfilter();

        // Get a roles that have the capability.
        $coursecontext = context_course::instance($data['course1']->id);
        $roles = get_role_names_with_caps_in_context($coursecontext, array('moodle/grade:edit'));

        // Enrol a user into the course, giving them the role.
        $roleid = 0;
        foreach ($roles as $rid => $role) {
            $roleid = $rid;
            $this->getDataGenerator()->enrol_user($data['user1']->id, $data['course1']->id, $roleid);
            break;
        }

        // Test: select category where no user with capability exists.
        $results = report_ncccscensus_teacher_search('teacher', array(), array(array('id' => $data['category2']->id)));
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayNotHasKey('id', $results[0]);

        // Test: select category where a user with capability exists
        $categories = array(array('id' => $data['category1']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        // Test: select all categories where user with capability exists
        $categories = array(array('id' => $data['category1']->id), array('id' => $data['category2']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        // Remove the capability from the user's role in the course.
        role_change_permission($roleid, $coursecontext, 'moodle/grade:edit', CAP_PREVENT);
        // Test: select all categories where no user with capability exists
        $categories = array(array('id' => $data['category1']->id), array('id' => $data['category2']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayNotHasKey('id', $results[0]);

        // Enrol another user to the course and give the user the capability.
        role_change_permission($roleid, $coursecontext, 'moodle/grade:edit', CAP_ALLOW);
        $this->getDataGenerator()->enrol_user($data['user2']->id, $data['course2']->id, $roleid);

        // Test: select category where a user with capability exists, this should return two results
        $categories = array(array('id' => $data['category1']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(2, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        $this->assertArrayHasKey('name', $results[1]);
        $this->assertArrayHasKey('id', $results[1]);
        $this->assertEquals($data['user2']->id, $results[1]['id']);
    }

    /**
     * This function tests the teacher searching when a course category is selected. Two users with the capability are in two
     * different courses that reside in two different course categories
     */
    public function test_teachersquery_filter_by_coursecategory_two_courses_different_categories() {
        global $CFG;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata_for_teacherfilter();

        // Assign role and remove capability.
        $coursecontext = context_course::instance($data['course1']->id);
        $roles = get_role_names_with_caps_in_context($coursecontext, array('moodle/grade:edit'));

        $roleid = 0;
        foreach ($roles as $rid => $role) {
            $roleid = $rid;
            $this->getDataGenerator()->enrol_user($data['user1']->id, $data['course1']->id, $roleid);
            $this->getDataGenerator()->enrol_user($data['user2']->id, $data['course3']->id, $roleid);
            break;
        }

        // Test: select category where a user with capability exists
        $categories = array(array('id' => $data['category1']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        // Test: select the second category where a user with capability exists
        $categories = array(array('id' => $data['category2']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user2']->id, $results[0]['id']);

        // Test: select both categories where users with capability exists
        $categories = array(array('id' => $data['category1']->id), array('id' => $data['category2']->id));
        $results = report_ncccscensus_teacher_search('teacher', array(), $categories);
        $this->assertEquals(2, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        $this->assertArrayHasKey('name', $results[1]);
        $this->assertArrayHasKey('id', $results[1]);
        $this->assertEquals($data['user2']->id, $results[1]['id']);
    }

    /**
     * This function tests the teacher searching when no course category or course is selected.
     */
    public function test_teachersquery_no_filter() {
        global $CFG;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot.'/report/ncccscensus/lib.php');
        $data = $this->createdata_for_teacherfilter();

        // Assign role and remove capability.
        $coursecontext = context_course::instance($data['course1']->id);
        $roles = get_role_names_with_caps_in_context($coursecontext, array('moodle/grade:edit'));

        $roleid = 0;
        foreach ($roles as $rid => $role) {
            $roleid = $rid;
            $this->getDataGenerator()->enrol_user($data['user1']->id, $data['course1']->id, $roleid);
            break;
        }

        // Test: one user is returned.
        $results = report_ncccscensus_teacher_search('teacher', array(), array());
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user1']->id, $results[0]['id']);

        // Remove the capability from the user's role in the course.
        role_change_permission($roleid, $coursecontext, 'moodle/grade:edit', CAP_PREVENT);

        // Test: no user is returned
        $results = report_ncccscensus_teacher_search('teacher', array(), array());
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayNotHasKey('id', $results[0]);

        // Test: one of two users are returned.
        $this->getDataGenerator()->enrol_user($data['user2']->id, $data['course3']->id, $roleid);
        $results = report_ncccscensus_teacher_search('teacher', array(), array());
        $this->assertEquals(1, count($results));
        $this->assertArrayHasKey('name', $results[0]);
        $this->assertArrayHasKey('id', $results[0]);
        $this->assertEquals($data['user2']->id, $results[0]['id']);
    }
}