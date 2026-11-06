<?php

if (!defined('ABSPATH')) exit;

/**
 * Textos editables del panel Help (FAQs & Contact) del dashboard.
 *
 * El admin administra categorias dinamicas (crear/quitar) desde el menu
 * "Help FAQ", cada una con un titulo y una lista de preguntas (solo texto,
 * sin link) que tambien se pueden agregar/quitar de a una.
 */
trait Aeropro_Help_Faq {
  private $help_faq_page_hook = '';

  private function register_help_faq_hooks() {
    add_action('admin_menu', [$this, 'help_faq_register_settings_page']);
    add_action('admin_init', [$this, 'help_faq_register_settings']);
    add_action('admin_enqueue_scripts', [$this, 'help_faq_enqueue_admin_assets']);
  }

  /** Contenido inicial (primera vez que se abre la pantalla, sin option guardada todavia). */
  private function help_faq_default_categories(): array {
    $default_questions = ['Questions'];

    return [
      ['title' => 'Getting started', 'questions' => $default_questions],
      ['title' => 'Studying', 'questions' => $default_questions],
      ['title' => 'Mock test and study sessions', 'questions' => $default_questions],
      ['title' => 'Plans and billing', 'questions' => $default_questions],
      ['title' => 'Help and support', 'questions' => $default_questions],
    ];
  }

  private function help_faq_categories(): array {
    $stored = get_option(self::OPTION_HELP_FAQ, null);
    if (is_array($stored) && !empty($stored)) {
      return array_values($stored);
    }

    return $this->help_faq_default_categories();
  }

  /** Secciones listas para el template del dashboard: titulo y preguntas (solo texto). */
  private function get_help_faq_sections(): array {
    $sections = [];

    foreach ($this->help_faq_categories() as $category) {
      $questions = [];
      foreach ((array) ($category['questions'] ?? []) as $question_text) {
        $text = (string) $question_text;
        if ($text === '') continue;

        $questions[] = $text;
      }

      $sections[] = [
        'label' => (string) ($category['title'] ?? ''),
        'questions' => $questions,
      ];
    }

    return $sections;
  }

  /* ---------- Backoffice ---------- */

  public function help_faq_register_settings_page() {
    $this->help_faq_page_hook = add_menu_page(
      'Help FAQ',
      'Help FAQ',
      'manage_options',
      self::HELP_FAQ_SETTINGS_SLUG,
      [$this, 'help_faq_render_settings_page'],
      'dashicons-editor-help',
      29
    );
  }

  public function help_faq_enqueue_admin_assets($hook) {
    if ($hook !== $this->help_faq_page_hook) return;

    wp_enqueue_script(
      'vc-help-faq-admin',
      AEROPRO_PLUGIN_URL . 'templates/assets/js/help-faq-admin.js',
      [],
      file_exists(AEROPRO_PLUGIN_DIR . 'templates/assets/js/help-faq-admin.js') ? (string) filemtime(AEROPRO_PLUGIN_DIR . 'templates/assets/js/help-faq-admin.js') : '1.0.0',
      true
    );
  }

  public function help_faq_register_settings() {
    register_setting(self::HELP_FAQ_SETTINGS_GROUP, self::OPTION_HELP_FAQ, [
      'type' => 'array',
      'sanitize_callback' => [$this, 'help_faq_sanitize_options'],
      'default' => [],
    ]);
  }

  public function help_faq_sanitize_options($input): array {
    $input = is_array($input) ? $input : [];
    $sanitized = [];

    foreach ($input as $category) {
      if (!is_array($category)) continue;

      $title = isset($category['title']) ? trim(sanitize_text_field(wp_unslash($category['title']))) : '';

      $questions = [];
      $raw_questions = isset($category['questions']) && is_array($category['questions']) ? $category['questions'] : [];
      foreach ($raw_questions as $question) {
        $q_text = is_array($question) ? '' : trim(sanitize_text_field(wp_unslash((string) $question)));
        if ($q_text === '') continue;

        $questions[] = $q_text;
      }

      // Fila vacia (sin titulo ni preguntas) = el admin la agrego y la borro de nuevo; se descarta.
      if ($title === '' && empty($questions)) continue;

      $sanitized[] = [
        'title' => $title,
        'questions' => $questions,
      ];
    }

    return $sanitized;
  }

  private function help_faq_render_category_row(string $name, string $cat_index, array $category): string {
    ob_start();
    ?>
    <div class="vc-help-faq-category" data-vc-help-faq-category data-vc-help-faq-cat-index="<?php echo esc_attr($cat_index); ?>">
      <div class="vc-help-faq-category-head">
        <label class="vc-help-faq-field">
          <span>Titulo de la categoria</span>
          <input type="text" class="regular-text"
            name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($cat_index); ?>][title]"
            value="<?php echo esc_attr((string) ($category['title'] ?? '')); ?>" placeholder="Ej: Getting started">
        </label>
        <button type="button" class="button vc-help-faq-remove" data-vc-help-faq-remove-category>Quitar categoria</button>
      </div>

      <div class="vc-help-faq-questions" data-vc-help-faq-questions>
        <?php foreach ((array) ($category['questions'] ?? []) as $q_index => $question_text): ?>
          <?php echo $this->help_faq_render_question_row($name, $cat_index, (string) $q_index, is_array($question_text) ? '' : (string) $question_text); ?>
        <?php endforeach; ?>
      </div>

      <button type="button" class="button" data-vc-help-faq-add-question>+ Agregar pregunta</button>
    </div>
    <?php
    return (string) ob_get_clean();
  }

  private function help_faq_render_question_row(string $name, string $cat_index, string $q_index, string $question_text): string {
    ob_start();
    ?>
    <div class="vc-help-faq-question-row" data-vc-help-faq-question-row>
      <label class="vc-help-faq-field">
        <span>Pregunta</span>
        <input type="text" class="regular-text"
          name="<?php echo esc_attr($name); ?>[<?php echo esc_attr($cat_index); ?>][questions][<?php echo esc_attr($q_index); ?>]"
          value="<?php echo esc_attr($question_text); ?>" placeholder="Texto de la pregunta">
      </label>
      <button type="button" class="button-link vc-help-faq-remove" data-vc-help-faq-remove-question>Quitar</button>
    </div>
    <?php
    return (string) ob_get_clean();
  }

  public function help_faq_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $categories = $this->help_faq_categories();
    $name = self::OPTION_HELP_FAQ;
    ?>
    <div class="wrap">
      <h1>Help FAQ</h1>
      <p>Categorias y preguntas del panel <em>Help</em> del dashboard (FAQs &amp; Contact). Son solo texto, sin link. Podes agregar o quitar categorias y preguntas libremente.</p>

      <style>
        .vc-help-faq-category { counter-reset: vc-help-faq-question; background: #fff; border: 1px solid #dcdcde; border-radius: 4px; padding: 16px; margin-bottom: 16px; }
        #vc-help-faq-categories { counter-reset: vc-help-faq-category; }
        .vc-help-faq-category { counter-increment: vc-help-faq-category; }
        .vc-help-faq-category-head { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px; }
        .vc-help-faq-category-head::before { content: "Categoria " counter(vc-help-faq-category) ": "; flex-basis: 100%; font-weight: 600; }
        .vc-help-faq-field { display: flex; flex-direction: column; gap: 4px; font-weight: 600; }
        .vc-help-faq-field span { font-weight: 600; }
        .vc-help-faq-questions { display: flex; flex-direction: column; gap: 10px; margin: 16px 0; padding-left: 16px; border-left: 3px solid #dcdcde; }
        .vc-help-faq-question-row { counter-increment: vc-help-faq-question; display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; }
        .vc-help-faq-question-row::before { content: "P" counter(vc-help-faq-question) ". "; align-self: center; font-weight: 600; color: #666; }
        .vc-help-faq-remove { color: #b32d2e; }
      </style>

      <form method="post" action="options.php">
        <?php settings_fields(self::HELP_FAQ_SETTINGS_GROUP); ?>

        <div id="vc-help-faq-categories">
          <?php foreach ($categories as $cat_index => $category): ?>
            <?php echo $this->help_faq_render_category_row($name, (string) $cat_index, is_array($category) ? $category : []); ?>
          <?php endforeach; ?>
        </div>

        <p><button type="button" class="button" id="vc-help-faq-add-category">+ Agregar categoria</button></p>

        <?php submit_button('Guardar cambios'); ?>
      </form>

      <template id="vc-help-faq-category-template"><?php echo $this->help_faq_render_category_row($name, '__CAT__', ['title' => '', 'questions' => []]); ?></template>
      <template id="vc-help-faq-question-template"><?php echo $this->help_faq_render_question_row($name, '__CAT__', '__Q__', ''); ?></template>
    </div>
    <?php
  }
}
