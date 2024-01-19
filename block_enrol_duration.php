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
 * Main code for Enrolment duration image block.
 *
 * @package   block_enrol_duration
 * @copyright  2012 Nathan Robbins (https://github.com/nrobbins)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_enrol_duration extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_enrol_duration');
    }

    function applicable_formats() {
        return array(
                     'all' => false,
                     'course' => true
                     );
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('pluginname', 'block_enrol_duration'));
    }

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;
        // require_once('lib.php'); //legacy

        if ($this->content !== null) {
            return $this->content;
        }

        $userid = $USER->id;
        $courseid = $this->page->course->id;

        $duration = $DB->get_record_sql('SELECT ue.timeend '.
                                        'FROM {user_enrolments} ue, {enrol} e '.
                                        'WHERE ue.userid = ? '.
                                        'AND ue.enrolid = e.id '.
                                        'AND e.courseid= ?', array($userid, $courseid));

        $this->content = new stdClass;

        if ($duration && ($duration->timeend > time())) {
            $days = ceil(($duration->timeend - time())/ 86400);
            $weeks = $days / 7;
            $date = getdate($duration->timeend);
            /* Future support for international date formats
            if($this->config->dateformat = 'mdy'){
                $fulldate = $date['month'] .' '. $date['mday'] .', '. $date['year'];
            } else {
                $fulldate = $date['mday'] .' '. $date['month'] .', '. $date['year'];
            }
            */
            $fulldate = $date['month'] .' '. $date['mday'] .', '. $date['year'];
            $coursename = $this->page->course->fullname;

            $this->content->text  = '<p>'.get_string('enrolmentin', 'block_enrol_duration').' <em>'.$coursename.'</em> '.
                                    get_string('expiresin', 'block_enrol_duration').'<br>';
            $this->content->text .= '<strong>'.$days.' '.get_string('days', 'block_enrol_duration').'</strong>';
            $this->content->text .= ': '.$fulldate.'.</p>';
        } else {
            $this->content->text  = '<p>'.get_string('enrolmentin', 'block_enrol_duration').' <em>'.$this->page->course->fullname.
                                    '</em> '.get_string('noexpiration', 'block_enrol_duration').'.</p>';
        }
        $this->content->footer = '';
        if(isset($this->config->information) && $this->config->information != '') {
            $this->content->footer = '<a href="'.$this->config->information.'"><img class="iconhelp" src="'.$OUTPUT->pix_url('docs')
            .'" alt="'.get_string('moreinformation', 'block_enrol_duration').'"> '
            .get_string('moreinformation', 'block_enrol_duration').'</a>';
        }

        return $this->content;
    }
}