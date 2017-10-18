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
 * IP restricted course format.
 *
 * @package format_iprestricted
 * @copyright 2017 Rossco Hellmans <rossco@catalyst-au.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ipranges = course_get_format($course)->get_course()->ipranges;
if (!remoteip_in_list($ipranges)) {
    die(get_string('ipblocked', 'format_iprestricted', getremoteaddr(null)));
}

require_once($CFG->dirroot. '/course/format/topics/format.php');
