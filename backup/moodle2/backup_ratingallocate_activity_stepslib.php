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
require_once(dirname(__FILE__) . '/backup_restore_helper.php');
use ratingallocate\db as this_db;
/**
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright  2014 C. Usener
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete ratingallocate structure for backup, with [file and] id annotations
 */
class backup_ratingallocate_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $class = 'ratingallocate\db\ratingallocate';
        $ratingallocate = new backup_nested_element(get_tablename_for_table_class($class),
            get_id_for_table_class($class), get_fields_for_table_class($class));

        $class = 'ratingallocate\db\ratingallocate_choices';
        $choices = new backup_nested_element(get_tablename_for_table_class($class) . 's');
        $choice = new backup_nested_element(get_tablename_for_table_class($class),
            get_id_for_table_class($class), get_fields_for_table_class($class));

        $class = 'ratingallocate\db\ratingallocate_ratings';
        $ratings = new backup_nested_element(get_tablename_for_table_class($class) . 's');
        $rating = new backup_nested_element(get_tablename_for_table_class($class),
            get_id_for_table_class($class), get_fields_for_table_class($class));

        $class = 'ratingallocate\db\ratingallocate_allocations';
        $allocations = new backup_nested_element(get_tablename_for_table_class($class) . 's');
        $allocation = new backup_nested_element(get_tablename_for_table_class($class),
            get_id_for_table_class($class), get_fields_for_table_class($class));

        // Build the tree.
        $ratingallocate->add_child($choices);
        $choices->add_child($choice);

        $choice->add_child($ratings);
        $ratings->add_child($rating);

        $choice->add_child($allocations);
        $allocations->add_child($allocation);

        // Define sources.
        $ratingallocate->set_source_table(
            get_tablename_for_table_class('ratingallocate\db\ratingallocate'),
                array(this_db\ratingallocate::ID => backup::VAR_ACTIVITYID),
                this_db\ratingallocate_choices::ID . ' ASC');
        $choice->set_source_table(
            get_tablename_for_table_class('ratingallocate\db\ratingallocate_choices'),
                array(this_db\ratingallocate_choices::RATINGALLOCATEID => backup::VAR_PARENTID),
                this_db\ratingallocate_choices::ID . ' ASC');

        if ($userinfo) {
            $rating->set_source_table(
                get_tablename_for_table_class('ratingallocate\db\ratingallocate_ratings'),
                    array(this_db\ratingallocate_ratings::CHOICEID => backup::VAR_PARENTID),
                    this_db\ratingallocate_ratings::ID . ' ASC');
            $allocation->set_source_table(
                get_tablename_for_table_class('ratingallocate\db\ratingallocate_allocations'),
                    array(this_db\ratingallocate_allocations::RATINGALLOCATEID => backup::VAR_ACTIVITYID,
                        this_db\ratingallocate_allocations::CHOICEID => backup::VAR_PARENTID),
                    this_db\ratingallocate_allocations::ID . ' ASC');
        }

        // Define id annotations.
        $allocation->annotate_ids('user', this_db\ratingallocate_allocations::USERID);
        $rating->annotate_ids('user', this_db\ratingallocate_ratings::USERID);

        // Define file annotations.
        $ratingallocate->annotate_files('mod_' . ratingallocate_MOD_NAME, 'intro', null);

        // Return the root element (ratingallocate), wrapped into standard activity structure.
        return $this->prepare_activity_structure($ratingallocate);
    }
}