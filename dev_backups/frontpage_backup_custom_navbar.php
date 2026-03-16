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

<!-- NAVBAR NUEVO (Estructura Limpia) -->
<div class="navbar fixed-top">
    <!-- Izquierda -->
    <div class="d-flex align-items-center h-100">
        <!-- LOGO RESTAURADO EXACTAMENTE COMO EN EL ANTIGUO -->
        <a href="<?php echo $CFG->wwwroot; ?>" class="navbar-brand">
            <img src="<?php echo $CFG->wwwroot; ?>/theme/moove/pix/icon_top.svg" alt="Proyecto Icon" class="logo">
        </a>

        <!-- Menú Custom -->
        <div class="custom-nav-links">
            <?php if ($is_admin): ?>
                <a href="<?php echo $CFG->wwwroot; ?>/admin/search.php" class="custom-nav-item">
                    <i class="fa fa-cogs"></i> Administración
                </a>
                <a href="<?php echo $CFG->wwwroot; ?>/course/index.php" class="custom-nav-item">
                    <i class="fa fa-list"></i> Cursos
                </a>
            <?php else: ?>
                <a href="<?php echo $home_url; ?>" class="custom-nav-item">
                    <i class="fa fa-home"></i> Página principal
                </a>
                <a href="#oferta-academica" class="custom-nav-item btn-available-courses">
                    <i class="fa fa-th-large"></i> Cursos disponibles
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Derecha -->
    <div id="usernavigation">
        <div class="lang-switch">
            <img src="<?php echo $CFG->wwwroot; ?>/theme/moove/pix/langicon.svg" alt="ES"> <span>ES</span>
        </div>
        
        <?php if (!isloggedin() or isguestuser()): ?>
             <a href="<?php echo $CFG->wwwroot; ?>/login/index.php">Ingresar</a>
        <?php else: ?>
             <?php echo $OUTPUT->user_menu(); ?>
        <?php endif; ?>
    </div>
</div>

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
