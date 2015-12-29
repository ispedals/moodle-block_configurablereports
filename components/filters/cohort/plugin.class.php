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

/** Configurable Reports
  * A Moodle block for creating customizable reports
  * @package blocks
  * @author: Abir Viqar
  * @date: 2015
  */

require_once($CFG->dirroot.'/blocks/configurable_reports/plugin.class.php');

class plugin_cohort extends plugin_base{

	function init(){
		$this->form = false;
		$this->unique = true;
		$this->fullname = get_string('filtercohort','block_configurable_reports');
		$this->reporttypes = array('categories','sql');
	}

	function summary($data){
		return get_string('filtercohort_summary','block_configurable_reports');
	}

	function execute($finalelements, $data){

		$filter_cohort = optional_param('filter_cohort',0,PARAM_INT);
		if(!$filter_cohort)
			return $finalelements;

		if ($this->report->type != 'sql') {
            return array($filter_cohort);
		} else {
			if (preg_match("/%%FILTER_COHORT:([^%]+)%%/i",$finalelements, $output)) {
				$replace = ' AND '.$output[1].' = '.$filter_cohort.' ';
				return str_replace('%%FILTER_COHORT:'.$output[1].'%%', $replace, $finalelements);
			}
		}
		return $finalelements;
	}

	function print_filter(&$mform){
		global $remotedb, $CFG;

		$filter_cohort = optional_param('filter_cohort',0,PARAM_INT);

		$reportclassname = 'report_'.$this->report->type;
		$reportclass = new $reportclassname($this->report);

        $cohortDB = $remotedb->get_records('cohort');
        $cohorts = array();
        foreach($cohortDB as $cohort) {
            $cohorts[$cohort->id] = $cohort->name;
        }

		if($this->report->type != 'sql'){
			$components = cr_unserialize($this->report->components);
			$conditions = $components['conditions'];

			$cohortlist = $reportclass->elements_by_conditions($conditions);
		} else {
			$cohortlist = $cohorts;
		}

        $cohortoptions = array();
        $cohortoptions[0] = get_string('filter_all', 'block_configurable_reports');

		if(!empty($cohortlist)){
			foreach($cohortlist as $key => $cohort){
				$cohortoptions[$key] = $cohort;
			}
		}

		$mform->addElement('select', 'filter_cohort', get_string('filtercohort','block_configurable_reports'), $cohortoptions);
		$mform->setType('filter_cohort', PARAM_INT);

	}

}
