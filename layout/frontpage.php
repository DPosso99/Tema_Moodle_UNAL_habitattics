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
 * Frontpage layout for theme_moove - Uses standard Moodle navbar
 *
 * @package    theme_moove
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB, $CFG, $OUTPUT, $PAGE, $SITE;

// --- CONSULTA DE CURSOS PARA TARJETAS ---
$sql = "SELECT c.id, c.fullname, c.summary, ctx.id as contextid, f.filename, f.filepath
          FROM {course} c
          JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = 50)
     LEFT JOIN {files} f ON (f.contextid = ctx.id 
                            AND f.component = 'course' 
                            AND f.filearea = 'overviewfiles' 
                            AND f.mimetype LIKE 'image/%'
                            AND f.filename != '.')
         WHERE c.visible = 1 
           AND c.id != 1
      ORDER BY c.sortorder ASC";

$raw_courses = $DB->get_records_sql($sql);

$coursecards = [];
$colors = ['#677d29de', '#677d29b8', '#364115bf', '#6fce0054'];
$i = 0;

foreach ($raw_courses as $course) {
    if (!empty($course->filename)) {
        $imageurl = moodle_url::make_pluginfile_url(
            $course->contextid, 
            'course', 
            'overviewfiles', 
            null, 
            $course->filepath, 
            $course->filename
        )->out();
    } else {
        $imageurl = 'https://via.placeholder.com/400x200/e0e0e0/ffffff?text=' . urlencode($course->fullname);
    }
    
    // Limpiar resumen: quitar HTML y limitar a 120 caracteres
    $summary = strip_tags($course->summary ?? '');
    if (strlen($summary) > 120) {
        $summary = substr($summary, 0, 117) . '...';
    }

    $coursecards[] = [
        'id'       => $course->id,
        'fullname' => format_string($course->fullname),
        'summary'  => $summary,
        'image'    => $imageurl,
        'color'    => $colors[$i % 4]
    ];
    $i++;
}

// --- PREPARAR CONTEXTO DEL TEMPLATE ---
$themesettings = new \theme_moove\util\settings();

// Clases extra para el body
$extraclasses = ['uses-drawers', 'pagelayout-frontpage'];
$bodyattributes = $OUTPUT->body_attributes($extraclasses);

// Primary navigation (navbar)
$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => \core\context\course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'primarymoremenu' => $primarymenu['moremenu'],
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'coursecards' => $coursecards,
];

// Agregar datos del footer
$templatecontext = array_merge($templatecontext, $themesettings->footer());

// Renderizar template
echo $OUTPUT->render_from_template('theme_moove/frontpage', $templatecontext);
