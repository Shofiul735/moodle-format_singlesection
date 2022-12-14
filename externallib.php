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
 * @package   format_singlesection
 * @copyright 2022, Md. Shofiul Islam
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');



function get_course_image($id)
{
    $url = '';

    $context = context_course::instance($id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);

    foreach ($files as $f) {
        if ($f->is_valid_image()) {
            $url = moodle_url::make_pluginfile_url($f->get_contextid(), $f->get_component(), $f->get_filearea(), null, $f->get_filepath(), $f->get_filename(), false);
        }
    }

    return $url;
}


function get_progress($course)
{
    global $USER;
    $percentage = core_completion\progress::get_course_progress_percentage($course, $USER->id);
    if (!is_null($percentage)) {
        return floor($percentage);
    } else {
        return 0;
    }
}

function get_first_activity_url($modules)
{
    $urlObj = null;
    foreach ($modules as $module) {
        // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
        if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
            continue;
        }
        // Module URL.
        $urlObj = new moodle_url($module->url, array('forceview' => 1));
        break;
    }
    return $urlObj;
}

function resumed_course_activity_url($course, $userid, $modules)
{
    $urlObj = null;
    $completion = new \completion_info($course);
    foreach ($modules as $module) {
        // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
        if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
            continue;
        }

        $data = $completion->get_data($module, true, $userid);
        $completed = $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
        // Module URL.
        if (!$completed) {
            $urlObj = new moodle_url($module->url, array('forceview' => 1));
            break;
        }
    }
    return $urlObj;
}
