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

/**
 * This file contains main class for the course format IP restricted
 *
 * @package   format_iprestricetd
 * @copyright 2017 Rossco Hellmans <rossco@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');
// This probably can be done in a better way.
$courseformats = get_sorted_course_formats(true);
foreach ($courseformats as $courseformat) {
    if ($courseformat === 'iprestricted') {
        continue;
    }
    require_once($CFG->dirroot. '/course/format/'. $courseformat .'/lib.php');
}

/**
 * Main class for the IP Restricted course format
 *
 * @package    format_iprestricted
 * @copyright  2017 Rossco Hellmans <rossco@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_iprestricted extends format_base {

    /**
     * Creates a new instance of class and sets the childformat object
     *
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     * @return format_base
     */
    protected function __construct($format, $courseid) {
        parent::__construct($format, $courseid);
        $course = $this->get_course();
        if (!is_null($course)) {
            $class = 'format_'. $course->childformat;
            $this->childformat = new $class($course->childformat, $courseid);
        }
    }

    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        return $this->call_childformat('get_section_name', array($section));
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        return true;
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        return $this->call_childformat('ajax_section_move');
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        return $this->call_childformat('get_view_url', array($section, $options));
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        $this->call_childformat('extend_course_navigation', array($navigation, $node));
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return $this->call_childformat('get_default_blocks');
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Topics format uses the following options:
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        $courseformats = get_sorted_course_formats(true);
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            if ($courseformat === 'iprestricted') {
                continue;
            }
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }

        $courseformatoptions = array(
            'ipranges' => array(
                'label' => get_string('ipranges', "format_iprestricted"),
                'element_type' => 'textarea',
                'help' => 'ipranges',
                'help_component' => 'format_iprestricted',
                'default' => getremoteaddr(null)
            ),
            'childformat' => array(
                'label' => get_string('childformat', "format_iprestricted"),
                'element_type' => 'select',
                'element_attributes' => array($formcourseformats)
            ) 
        );
        if (isset($this->childformat)) {
            $childoptions = $this->childformat->course_format_options($foreditform);
            $courseformatoptions = array_merge($courseformatoptions, $childoptions);
         } else {
            $courseid = $this->get_courseid();
            foreach ($courseformats as $courseformat) {
                if ($courseformat === 'iprestricted') {
                    continue;
                }

                $class = 'format_'. $courseformat;
                $format = new $class($courseformat, $courseid);
                $extraformatoptions = $format->course_format_options($foreditform);
                $courseformatoptions = array_merge($courseformatoptions, $extraformatoptions);
            }
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        $elements = parent::create_edit_form_elements($mform, $forsection);
        
        if (isset($this->childformat)) {
            $childelements = $this->childformat->create_edit_form_elements($mform, $forsection);
            $elements = array_merge($elements, $childelements);
        } else {   
            $courseid = $this->get_courseid();
            $courseformats = get_sorted_course_formats(true);

            foreach ($courseformats as $courseformat) {
                if ($courseformat === 'iprestricted') {
                    continue;
                }

                $class = 'format_'. $courseformat;
                $format = new $class($courseformat, $courseid);
                $extraelements = $format->create_edit_form_elements($mform, $forsection);
                foreach($extraelements as $key => $extraelement) {
                    $name = $extraelement->getName();
                    if ($name !== '' && $mform->elementExists($name)) {
                        unset($extraelements[$key]);
                        $mform->removeElement($name);
                    }
                }
                $elements = array_merge($elements, $extraelements);
            }
        }

        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'topics', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        $changed = $this->update_format_options($data);
        
        if (isset($this->childformat)) {
            $childchanged = $this->childformat->update_course_format_options($data, $oldcourse);
            $changed = $changed || $childchanged;
        } else {
            $courseformats = get_sorted_course_formats(true);
            $courseid = $this->get_courseid();
            foreach ($courseformats as $courseformat) {
                if ($courseformat === 'iprestricted') {
                    continue;
                }

                $class = 'format_'. $courseformat;
                $format = new $class($courseformat, $courseid);
                $extrachanged = $format->update_course_format_options($data, $oldcourse);
                $changed = $changed || $extrachanged;
            }
        }
        return $changed;
    }

    /**
     * Returns true if the specified section is current
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function is_section_current($section) {
        return $this->call_childformat('is_section_current', array($section));
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return $this->call_childformat('can_delete_section', array($section));
    }

    /**
     * Call the child format if it exists, else call the format_base parent
     *
     * @param array $args arguments to be passed
     * @return mixed
     */
    protected function call_childformat($method, $args = array()) {
        if (!is_null($this->childformat)) {
            return call_user_func_array(array($this->childformat, $method), $args);
        } else {
            return call_user_func_array(array('parent', $method), $args);
        }
    }


}
