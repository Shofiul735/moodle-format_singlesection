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
 * Renderer for outputting the newsection course format.
 *
 * @package format_newsection
 * @copyright 2022 Md. Shofiul
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */

namespace format_newsection\output;

use core_courseformat\output\section_renderer;
use html_writer;

require_once(__DIR__ . '/../../../../../config.php');
require_once(__DIR__ . '/../../externallib.php');



/**
 * Basic renderer for newsection format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer
{

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course)
    {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course)
    {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }



    public function display($course, $orphaned)
    {
        global $USER, $COURSE, $PAGE;
        $format = course_get_format($course);
        $modinfo = $format->get_modinfo();
        $courseinfo = $format->get_course();
        $cmlistclass = $format->get_output_classname('content\\section\\cmlist');

        // Checking if the user is a student
        $isstudent = user_has_role_assignment($USER->id, 5);



        $output = '';
        if ($isstudent) {

            $url = get_first_activity_url($modinfo->get_cms());
            $url = resumed_course_activity_url($course, $USER->id, $modinfo->get_cms()) ?? $url;

            $imageurl = get_course_image($courseinfo->id);
            $progress = get_progress($courseinfo);

            $buttontext = null;
            if ($progress === 0) {
                $buttontext = 'Start Course';
            } else {
                $buttontext = 'Resume Course';
            }

            echo html_writer::start_div();
            echo html_writer::tag('h1', $courseinfo->fullname);
            echo html_writer::end_div();
            echo html_writer::img($imageurl, 'course image', ['width' => '100%']);
            echo html_writer::start_div('text-justify mt-5');
            echo html_writer::tag('h3', 'Course Summary:', ['class' => 'text-primary']);
            echo html_writer::tag('h6', $courseinfo->summary, ['class' => 'text-secondary']);
            echo html_writer::start_div('d-flex justify-content-between align-items-center');
            echo html_writer::tag('a', $buttontext, ['href' => $url, 'class' => 'btn btn-outline-success', 'type' => 'submit']);
            echo html_writer::start_div();
            echo html_writer::tag('p', "Course Progress: {$progress}% ", ['class' => 'text-success']);
            echo html_writer::tag('progress', "{$progress}%", ['max' => 100, 'value' => $progress]);
            echo html_writer::end_div();
            echo html_writer::end_div();
            echo html_writer::end_div();

            return;
        } else {
            if ($orphaned) {
                if (!empty($modinfo->sections[1])) {
                    $output .= $this->output->heading(get_string('orphaned', 'format_newsection'), 3, 'sectionname');
                    $output .= $this->output->box(get_string('orphanedwarning', 'format_newsection'));

                    $section = $modinfo->get_section_info(1);
                    $output .= $this->render(new $cmlistclass($format, $section));
                }
            } else {
                $section = $modinfo->get_section_info(0);
                $output .= $this->render(new $cmlistclass($format, $section));

                if (empty($modinfo->sections[0]) && course_get_format($course)) {
                    // Course format was unable to automatically redirect to add module page.
                    $output .= $this->course_section_add_cm_control($course, 0, 0);
                }
            }
            return $output;
        }



        return $output;
    }
}
