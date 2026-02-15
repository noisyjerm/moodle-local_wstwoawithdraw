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
 * Settings for local_wstwoawithdraw.
 *
 * @package     local_wstwoawithdraw
 * @author      Jeremy FitzPatrick <jeremy.fitzpatrick@twoa.ac.nz>>
 * @copyright   2025, Te Wānanga o Aotearoa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $pluginsettings = new admin_settingpage('local_ws_twoawithdraw', get_string('pluginname', 'local_wstwoawithdraw'));
    $name = new lang_string('graceperiod', 'local_wstwoawithdraw');
    $description = new lang_string('graceperiod_help', 'local_wstwoawithdraw');
    $graceperiod = 21 * (24 * 60 * 60);
    $setting = new admin_setting_configduration('local_ws_twoawithdraw/graceperiod',
        $name,
        $description,
        $graceperiod);
    $pluginsettings->add($setting);

    $ADMIN->add('localplugins', $pluginsettings);
}

$ADMIN->add("localplugins", new admin_externalpage(
    'local_wstwoawithdraw_history',
    get_string('history', 'local_wstwoawithdraw'),
    new moodle_url("/local/wstwoawithdraw/report.php")
));
