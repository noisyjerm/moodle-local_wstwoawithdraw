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
 * Version file for local_wstwoawithdraw.
 *
 * @package     local_wstwoawithdraw
 * @author      Jeremy FitzPatrick <jeremy.fitzpatrick@twoa.ac.nz>>
 * @copyright   2025, Te Wānanga o Aotearoa
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No direct access.
defined('MOODLE_INTERNAL') || die();

// This plugin requires Moodle 4.5.
$plugin->requires = 2024100700;

// Plugin details.
$plugin->component  = 'local_wstwoawithdraw';
$plugin->version    = 2025090104;   // Plugin updated Mahuru 2025.
$plugin->release    = 'v4.5-r1';

// Plugin status details.
$plugin->maturity = MATURITY_ALPHA;   // ALPHA, BETA, RC, STABLE.