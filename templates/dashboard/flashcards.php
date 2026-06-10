<?php
if (!defined('ABSPATH')) exit;

$render_stat_icon = static function (string $icon): string {
  switch ($icon) {
    case 'trend':
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16 5-5 4 4 7-7M14 8h6v6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    case 'badge':
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l5 2v5c0 4.1-2.8 7.8-5 8.9-2.2-1.1-5-4.8-5-8.9V5l5-2Zm0 11v7m-3 0h6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    case 'trophy':
    default:
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4h8v3a4 4 0 0 1-8 0V4Zm8 1h3a1 1 0 0 1 1 1 4 4 0 0 1-4 4M8 5H5a1 1 0 0 0-1 1 4 4 0 0 0 4 4m0 8h8m-7 0v2h6v-2" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  }
};
?>
<section class="vc-dashboard-summary-grid">
  <?php foreach ($summary_stats as $stat): ?>
    <article class="vc-dashboard-summary-card">
      <span class="vc-dashboard-summary-icon"><?php echo $render_stat_icon((string) ($stat['icon'] ?? 'trophy')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
      <span class="vc-dashboard-summary-copy">
        <small><?php echo esc_html((string) ($stat['label'] ?? '')); ?></small>
        <strong><?php echo esc_html((string) ($stat['value'] ?? '')); ?></strong>
      </span>
    </article>
  <?php endforeach; ?>
</section>

<section class="vc-dashboard-subject-grid">
  <?php foreach ($subjects as $subject): ?>
    <?php
    $progress = isset($subject['progress']) ? max(0, min(100, (int) $subject['progress'])) : 0;
    $cta_url = !empty($subject['cta_url']) ? (string) $subject['cta_url'] : '#';
    $cta_label = !empty($subject['cta_label']) ? (string) $subject['cta_label'] : 'Study';
    ?>
    <article class="vc-dashboard-subject-card">
      <h3><?php echo esc_html((string) ($subject['title'] ?? '')); ?></h3>

      <div class="vc-dashboard-progress-row">
        <span>Progress</span>
        <strong><?php echo esc_html($progress . '%'); ?></strong>
      </div>

      <div class="vc-dashboard-progress-bar" aria-hidden="true">
        <span style="width: <?php echo esc_attr((string) $progress); ?>%;"></span>
      </div>

      <p class="vc-dashboard-subject-meta"><?php echo esc_html((string) ($subject['meta'] ?? '')); ?></p>

      <a class="vc-dashboard-button" href="<?php echo esc_url($cta_url); ?>">
        <span><?php echo esc_html($cta_label); ?></span>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </article>
  <?php endforeach; ?>
</section>

<section class="vc-dashboard-global-card">
  <div class="vc-dashboard-global-icon">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 0c2.5 2.7 4 6.2 4 10s-1.5 7.3-4 10m0-20C9.5 4.7 8 8.2 8 12s1.5 7.3 4 10m-9-10h18M4.5 7h15M4.5 17h15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
  </div>
  <div class="vc-dashboard-global-copy">
    <h3><?php echo esc_html((string) ($global_card['title'] ?? 'Global Random Practice')); ?></h3>
    <p><?php echo esc_html((string) ($global_card['description'] ?? '')); ?></p>
  </div>
  <a class="vc-dashboard-button vc-dashboard-button--secondary" href="<?php echo esc_url((string) ($global_card['cta_url'] ?? '#')); ?>">
    <?php echo esc_html((string) ($global_card['cta_label'] ?? 'Start study')); ?>
  </a>
</section>
