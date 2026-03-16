<?php
defined('MOODLE_INTERNAL') || die();

global $DB, $CFG, $OUTPUT, $USER;

// --- CONSULTA SEGURA DE IMÁGENES ---
$sql = "SELECT c.id, c.fullname, ctx.id as contextid, f.filename, f.filepath
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

$raw_courses = $DB->get_records_sql($sql, null, 0, 4);

$cards_data = [];
$colors = ['#00aec7', '#001f3f', '#f06e6b', '#e85d9e']; 
$i = 0;

foreach ($raw_courses as $course) {
    if (!empty($course->filename)) {
        $imageurl = moodle_url::make_pluginfile_url($course->contextid, 'course', 'overviewfiles', 0, $course->filepath, $course->filename)->out();
    } else {
        $imageurl = 'https://via.placeholder.com/400x200/e0e0e0/ffffff?text=' . urlencode($course->fullname);
    }
    $cards_data[] = ['id' => $course->id, 'fullname' => $course->fullname, 'image' => $imageurl, 'color' => $colors[$i % 4]];
    $i++;
}

// Construir templatecontext para navbar
require_once($CFG->dirroot . '/course/lib.php');

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

$extraclasses = [];
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => \core\context\course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'primarymoremenu' => $primarymenu['moremenu'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
];

$is_admin = is_siteadmin();
$home_url = new moodle_url('/my/');

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php
// Renderizar navbar estándar (navbar.mustache)
echo $OUTPUT->render_from_template('theme_moove/navbar', $templatecontext);
?>

<!-- HÉROE -->
<div class="hero-container">
    <div class="hero-text">
        <h1>Proyecto <span>Hábitat TICs</span></h1>
        <p>Desde sus raíces sociales hasta el entorno digital, conoce todo sobre la transformación del hábitat con tecnologías de la información.</p>
        <a href="#oferta-academica" class="btn-hero">Conoce nuestra oferta</a>
    </div>
    <div class="hero-video">
        <div class="video-mask">
            <video autoplay muted loop playsinline>
                <source src="<?php echo $CFG->wwwroot; ?>/theme/moove/pix/banner.mp4" type="video/mp4">
            </video>
        </div>
    </div>
</div>

<!-- TARJETAS -->
<div id="oferta-academica" class="offer-section">
    <div class="offer-grid">
        <?php foreach ($cards_data as $card): ?>
        <a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $card['id']; ?>" class="offer-card">
            <img src="<?php echo $card['image']; ?>" alt="<?php echo $card['fullname']; ?>">
            <div class="offer-footer" style="background-color: <?php echo $card['color']; ?>;">
                <?php echo format_string($card['fullname']); ?>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<footer class="custom-footer">
    <div class="footer-links">
        <a href="mailto:adminhabitattics@proyectohabitattic.site">E-mail: adminhabitattics@proyectohabitattic.site</a> |
        <a href="https://wa.me/573156727930" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <div class="credits">Desarrollado con ♥ por el equipo de Proyecto Hábitat TICs</div>
</footer>

<!-- Contenido Oculto Obligatorio -->
<div id="hidden-main-content"><?php echo $OUTPUT->main_content(); ?></div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>
