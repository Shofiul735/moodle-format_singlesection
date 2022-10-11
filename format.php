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
 * singlesection course format.  Display the whole course as "singlesection" made of modules.
 *
 * @package format_singlesection
 * @copyright 2022 Md. Shofiul Islam
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

$CFG->cachejs = false;

// Horrible backwards compatible parameter aliasing..
if ($singlesection = optional_param('singlesection', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $singlesection);
    debugging('Outdated singlesection param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$format = course_get_format($course);

// Make sure section 0 is created.
course_create_sections_if_missing($format->get_course(), 0);

$renderer = $PAGE->get_renderer('format_singlesection');

// Checking if the user is a student
$isStudent = user_has_role_assignment($USER->id, 5);

if ($isStudent) {
    $renderer->display($course, $section != 0);
} else {
    $renderer->print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
    $PAGE->requires->js('/course/format/singlesection/format.js');
}
